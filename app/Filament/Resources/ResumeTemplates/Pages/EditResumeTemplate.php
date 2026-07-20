<?php

namespace App\Filament\Resources\ResumeTemplates\Pages;

use App\Filament\Resources\ResumeTemplates\ResumeTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditResumeTemplate extends EditRecord
{
    protected static string $resource = ResumeTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
