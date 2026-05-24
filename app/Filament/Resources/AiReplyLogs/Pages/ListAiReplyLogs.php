<?php

namespace App\Filament\Resources\AiReplyLogs\Pages;

use App\Filament\Resources\AiReplyLogs\AiReplyLogResource;
use Filament\Resources\Pages\ListRecords;

class ListAiReplyLogs extends ListRecords
{
    protected static string $resource = AiReplyLogResource::class;

    protected function getHeaderWidgets(): array
    {
        return AiReplyLogResource::getWidgets();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
