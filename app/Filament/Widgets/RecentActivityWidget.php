<?php

namespace App\Filament\Widgets;

use App\Models\AuditLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentActivityWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AuditLog::query()->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('User'),
                Tables\Columns\TextColumn::make('event')->label('Activity'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ]);
    }
}
