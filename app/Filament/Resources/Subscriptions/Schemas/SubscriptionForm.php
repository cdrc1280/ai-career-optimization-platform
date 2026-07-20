<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('plan_id')
                    ->relationship('plan', 'name')
                    ->required(),
                Select::make('status')
                    ->options([
            'trialing' => 'Trialing',
            'active' => 'Active',
            'past_due' => 'Past due',
            'canceled' => 'Canceled',
            'expired' => 'Expired',
        ])
                    ->default('active')
                    ->required(),
                DateTimePicker::make('current_period_start'),
                DateTimePicker::make('current_period_end'),
                DateTimePicker::make('canceled_at'),
                TextInput::make('payment_provider'),
                TextInput::make('payment_provider_subscription_id'),
            ]);
    }
}
