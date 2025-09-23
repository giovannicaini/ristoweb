<?php

namespace App\Filament\Resources\PostazioneResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use App\Filament\Resources\PostazioneResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPostazione extends EditRecord
{
    protected static string $resource = PostazioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
