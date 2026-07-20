<?php

namespace App\Filament\Resources\Subscriptions;

use App\Filament\Resources\Subscriptions\Pages;
use App\Models\Subscription;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\Select::make('plan_id')
                    ->relationship('plan', 'name')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'canceled' => 'Canceled',
                        'past_due' => 'Past Due',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('starts_at'),
                Forms\Components\DatePicker::make('ends_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->searchable(),
                Tables\Columns\TextColumn::make('plan.name')->searchable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('starts_at')->date(),
                Tables\Columns\TextColumn::make('ends_at')->date(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'canceled' => 'Canceled',
                        'past_due' => 'Past Due',
                    ]),
                Tables\Filters\SelectFilter::make('plan_id')
                    ->relationship('plan', 'name'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
