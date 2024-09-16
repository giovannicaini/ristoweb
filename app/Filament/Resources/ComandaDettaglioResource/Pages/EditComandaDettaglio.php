<?php

namespace App\Filament\Resources\ComandaDettaglioResource\Pages;

use App\Filament\Resources\ComandaDettaglioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComandaDettaglio extends EditRecord
{
    protected static string $resource = ComandaDettaglioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
