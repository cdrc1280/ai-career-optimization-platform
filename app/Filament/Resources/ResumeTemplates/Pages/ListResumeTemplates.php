<?php

namespace App\Filament\Resources\ResumeTemplates\Pages;

use App\Filament\Resources\ResumeTemplates\ResumeTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListResumeTemplates extends ListRecords
{
    protected static string $resource = ResumeTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
