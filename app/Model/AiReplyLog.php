<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiReplyLog extends Model
{
    protected $table = 'ai_reply_logs';

    protected $fillable = [
        'creator_user_id',
        'fan_user_id',
        'trigger_message_id',
        'reply_message_id',
        'tokens_used',
        'model',
    ];

    protected $casts = [
        'tokens_used' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    public function fan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fan_user_id');
    }

    public function triggerMessage(): BelongsTo
    {
        return $this->belongsTo(UserMessage::class, 'trigger_message_id');
    }

    public function replyMessage(): BelongsTo
    {
        return $this->belongsTo(UserMessage::class, 'reply_message_id');
    }
}
