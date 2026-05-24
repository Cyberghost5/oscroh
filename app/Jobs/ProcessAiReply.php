<?php

namespace App\Jobs;

use App\Services\AiReplyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAiReply implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Max attempts before the job is marked as failed.
     */
    public int $tries = 2;

    /**
     * Seconds before the job is considered timed out.
     */
    public int $timeout = 60;

    public function __construct(
        public readonly int $triggerMessageId,
        public readonly int $creatorId,
        public readonly int $fanId,
    ) {}

    public function handle(AiReplyService $service): void
    {
        $service->handle($this->triggerMessageId, $this->creatorId, $this->fanId);
    }
}
