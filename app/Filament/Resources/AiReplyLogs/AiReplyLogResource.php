<?php

namespace App\Filament\Resources\AiReplyLogs;

use App\Filament\Resources\AiReplyLogs\Pages\ListAiReplyLogs;
use App\Filament\Resources\AiReplyLogs\Widgets\AiReplyStats;
use App\Model\AiReplyLog;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use UnitEnum;

class AiReplyLogResource extends Resource
{
    protected static ?string $model = AiReplyLog::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static UnitEnum|string|null $navigationGroup = 'AI';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return 'AI Reply Log';
    }

    public static function getPluralModelLabel(): string
    {
        return 'AI Reply Logs';
    }

    public static function getWidgets(): array
    {
        return [
            AiReplyStats::class,
        ];
    }

    /**
     * This resource is read-only — no create/edit forms needed.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('creator.username')
                    ->label('Creator')
                    ->searchable()
                    ->sortable()
                    ->url(fn (AiReplyLog $record): string => $record->creator
                        ? route('profile', ['username' => $record->creator->username])
                        : '#'
                    )
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('fan.username')
                    ->label('Fan')
                    ->searchable()
                    ->sortable()
                    ->url(fn (AiReplyLog $record): string => $record->fan
                        ? route('profile', ['username' => $record->fan->username])
                        : '#'
                    )
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('replyMessage.message')
                    ->label('Reply Preview')
                    ->limit(60)
                    ->tooltip(fn (AiReplyLog $record): ?string => $record->replyMessage?->message)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('tokens_used')
                    ->label('Tokens')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('model')
                    ->label('Model')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                QueryBuilder::make()
                    ->constraints([
                        DateConstraint::make('created_at')->label('Date'),
                        NumberConstraint::make('tokens_used')->label('Tokens Used'),
                        TextConstraint::make('model')->label('Model'),
                    ]),
            ])
            ->actions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAiReplyLogs::route('/'),
        ];
    }

    /**
     * Disable the create button — logs are system-generated only.
     */
    public static function canCreate(): bool
    {
        return false;
    }
}
