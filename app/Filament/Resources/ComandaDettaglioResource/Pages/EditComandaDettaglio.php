<?php

namespace App\Filament\Resources\ComandaDettaglioResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use App\Filament\Resources\ComandaDettaglioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComandaDettaglio extends EditRecord
{
    protected static string $resource = ComandaDettaglioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
