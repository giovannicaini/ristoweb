<?php

namespace App\Filament\Resources\ComandaDettaglioResource\Pages;

use App\Filament\Resources\ComandaDettaglioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListComandaDettaglios extends ListRecords
{
    protected static string $resource = ComandaDettaglioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
