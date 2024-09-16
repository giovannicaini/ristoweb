<?php

namespace App\Filament\Resources\PostazioneResource\Pages;

use App\Filament\Resources\PostazioneResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPostazione extends EditRecord
{
    protected static string $resource = PostazioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
