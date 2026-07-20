<?php

namespace App\Filament\Resources\AiPrompts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AiPromptForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Textarea::make('prompt_text')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('description'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
