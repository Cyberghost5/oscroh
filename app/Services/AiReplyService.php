<?php

namespace App\Services;

use App\Events\NewUserMessage;
use App\Model\User;
use App\Model\UserMessage;
use App\Providers\GenericHelperServiceProvider;
use App\Settings\AISettings;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AiReplyService
{
    /**
     * Main entry point called from the ProcessAiReply job.
     * Checks all eligibility gates, calls OpenAI, saves the reply, and broadcasts it.
     */
    public function handle(int $triggerMessageId, int $creatorId, int $fanId): void
    {
        try {
            $creator        = User::find($creatorId);
            $fan            = User::find($fanId);
            $triggerMessage = UserMessage::find($triggerMessageId);

            if (!$creator || !$fan || !$triggerMessage) {
                return;
            }

            /** @var AISettings $settings */
            $settings = app(AISettings::class);

            // Gate 1: Global AI auto-reply switch
            if (!$settings->ai_auto_reply_enabled) {
                return;
            }

            // Gate 2: Admin has granted this feature to the creator
            if (!$creator->ai_auto_reply_enabled) {
                return;
            }

            // Gate 3: Creator has not paused it themselves
            $creatorSettings = is_array($creator->settings) ? $creator->settings : [];
            if (($creatorSettings['ai_auto_reply_paused'] ?? 'false') === 'true') {
                return;
            }

            // Gate 4: Paid message — only respond if creator allows it
            if (($triggerMessage->price ?? 0) > 0 && !$creator->ai_auto_reply_respond_to_paid) {
                return;
            }

            // Gate 5: Conversation reply limit
            $maxReplies = (int) ($settings->ai_auto_reply_max_per_conversation ?? 10);
            $replyCount = DB::table('ai_reply_logs')
                ->where('creator_user_id', $creatorId)
                ->where('fan_user_id', $fanId)
                ->count();

            if ($replyCount >= $maxReplies) {
                return;
            }

            // Build conversation history for context
            $contextCount = max(1, (int) ($settings->ai_auto_reply_context_messages ?? 10));
            $history = UserMessage::where(function ($q) use ($creatorId, $fanId) {
                $q->where('sender_id', $creatorId)->where('receiver_id', $fanId);
            })->orWhere(function ($q) use ($creatorId, $fanId) {
                $q->where('sender_id', $fanId)->where('receiver_id', $creatorId);
            })->orderBy('id', 'desc')->limit($contextCount)->get()->reverse()->values();

            // Resolve system prompt (creator override → global default → hardcoded fallback)
            $systemPrompt = !empty(trim((string) $creator->ai_auto_reply_system_prompt))
                ? (string) $creator->ai_auto_reply_system_prompt
                : (string) ($settings->ai_auto_reply_system_prompt ?? '');

            $systemPrompt = str_replace('{creator_name}', $creator->username, $systemPrompt);

            if (empty(trim($systemPrompt))) {
                $systemPrompt = "You are {$creator->username}, a popular content creator. "
                    . "Reply in a warm, engaging, and slightly flirty tone as if personally chatting with a fan. "
                    . "Keep replies short (1–3 sentences). "
                    . "Where natural, hint that you love receiving tips from fans.";
            }

            // Build the OpenAI messages array
            $messages = [['role' => 'system', 'content' => $systemPrompt]];

            foreach ($history as $msg) {
                // From the creator's perspective: creator's own past messages = assistant, fan's = user
                $role = ($msg->sender_id === $creatorId) ? 'assistant' : 'user';
                if (!empty($msg->message)) {
                    $messages[] = ['role' => $role, 'content' => (string) $msg->message];
                }
            }

            // Resolve model and API key
            $model  = !empty($settings->ai_auto_reply_model) ? $settings->ai_auto_reply_model : 'gpt-4o';
            $apiKey = trim((string) ($settings->open_ai_api_key ?? ''));

            if (empty($apiKey)) {
                Log::warning('AiReplyService: OpenAI API key is not configured.');
                return;
            }

            // Build request body
            $requestBody = ['model' => $model, 'messages' => $messages];

            // Models that don't support max_tokens
            if (!in_array($model, ['o4-mini', 'o3', 'gpt-5-chat-latest', 'o1', 'o1-mini'])) {
                $requestBody['max_tokens'] = 300;
            }

            // Call OpenAI
            $client   = new Client(['timeout' => 30]);
            $response = $client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $apiKey,
                ],
                'body' => json_encode($requestBody),
            ]);

            $data       = json_decode($response->getBody(), true);
            $replyText  = trim((string) ($data['choices'][0]['message']['content'] ?? ''));
            $tokensUsed = $data['usage']['total_tokens'] ?? null;

            if (empty($replyText)) {
                Log::warning('AiReplyService: Received empty reply from OpenAI.', ['creator_id' => $creatorId]);
                return;
            }

            // Persist the AI reply as a real UserMessage from the creator
            $replyMessage = UserMessage::create([
                'sender_id'   => $creatorId,
                'receiver_id' => $fanId,
                'message'     => $replyText,
                'price'       => 0,
                'isSeen'      => 0,
            ]);

            // Log the AI activity
            DB::table('ai_reply_logs')->insert([
                'creator_user_id'    => $creatorId,
                'fan_user_id'        => $fanId,
                'trigger_message_id' => $triggerMessageId,
                'reply_message_id'   => $replyMessage->id,
                'tokens_used'        => $tokensUsed,
                'model'              => $model,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            // Build and broadcast the payload to both users' shared private channel
            $payload = $this->buildBroadcastPayload($replyMessage, $creator, $fan);
            broadcast(new NewUserMessage(json_encode($payload), $creatorId, $fanId));

        } catch (\Throwable $e) {
            Log::error('AiReplyService error: ' . $e->getMessage(), [
                'creator_id'         => $creatorId,
                'fan_id'             => $fanId,
                'trigger_message_id' => $triggerMessageId,
            ]);
        }
    }

    /**
     * Builds a broadcast payload that matches the shape the Messenger frontend expects.
     * Mirrors the logic of MessengerController::cleanUpMessageData() without Auth context.
     */
    private function buildBroadcastPayload(UserMessage $message, User $creator, User $fan): UserMessage
    {
        $allowedFields = ['id', 'username', 'avatar', 'name'];

        $senderArr   = collect($creator->toArray())->only($allowedFields)->toArray();
        $receiverArr = collect($fan->toArray())->only($allowedFields)->toArray();

        $senderArr['profileUrl']   = route('profile', ['username' => $creator->username]);
        $receiverArr['profileUrl'] = route('profile', ['username' => $fan->username]);
        $senderArr['canEarnMoney'] = GenericHelperServiceProvider::creatorCanEarnMoney($creator);

        $message->setAttribute('sender', $senderArr);
        $message->setAttribute('receiver', $receiverArr);
        $message->setAttribute('attachments', []);
        $message->setAttribute('hasUserUnlockedMessage', false);
        $message->setAttribute('story_ref', null);
        $message->setAttribute('dateAdded', $message->created_at->diffForHumans(null, true, true));

        return $message;
    }
}
