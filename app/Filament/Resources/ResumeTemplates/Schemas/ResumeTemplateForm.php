<?php

namespace App\Filament\Resources\ResumeTemplates\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ResumeTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('file_path')
                    ->required(),
                Toggle::make('is_default')
                    ->required(),
            ]);
    }
}
