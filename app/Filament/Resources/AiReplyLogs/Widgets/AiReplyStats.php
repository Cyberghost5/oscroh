<?php

namespace App\Filament\Resources\AiReplyLogs\Widgets;

use App\Filament\Resources\AiReplyLogs\Pages\ListAiReplyLogs;
use App\Model\AiReplyLog;
use App\Model\User;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class AiReplyStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListAiReplyLogs::class;
    }

    protected function getStats(): array
    {
        $totalReplies = AiReplyLog::count();
        $totalTokens  = (int) AiReplyLog::sum('tokens_used');
        $activeCreators = User::where('ai_auto_reply_enabled', true)->count();

        return [
            Stat::make('Total AI Replies', Number::format($totalReplies))
                ->description('All-time AI-generated replies')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('primary'),

            Stat::make('Total Tokens Used', Number::format($totalTokens))
                ->description('Cumulative OpenAI tokens consumed')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('warning'),

            Stat::make('Active AI Creators', Number::format($activeCreators))
                ->description('Creators with AI auto-reply enabled')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
        ];
    }
}
