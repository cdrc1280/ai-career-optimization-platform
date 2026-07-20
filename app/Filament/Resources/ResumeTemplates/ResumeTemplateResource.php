<?php

namespace App\Filament\Resources\ResumeTemplates;

use App\Filament\Resources\ResumeTemplates\Pages\CreateResumeTemplate;
use App\Filament\Resources\ResumeTemplates\Pages\EditResumeTemplate;
use App\Filament\Resources\ResumeTemplates\Pages\ListResumeTemplates;
use App\Filament\Resources\ResumeTemplates\Schemas\ResumeTemplateForm;
use App\Filament\Resources\ResumeTemplates\Tables\ResumeTemplatesTable;
use App\Models\ResumeTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ResumeTemplateResource extends Resource
{
    protected static ?string $model = ResumeTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ResumeTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResumeTemplatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResumeTemplates::route('/'),
            'create' => CreateResumeTemplate::route('/create'),
            'edit' => EditResumeTemplate::route('/{record}/edit'),
        ];
    }
}
