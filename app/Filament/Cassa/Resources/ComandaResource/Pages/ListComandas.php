<?php

namespace App\Filament\Cassa\Resources\ComandaResource\Pages;

use App\Filament\Cassa\Resources\ComandaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListComandas extends ListRecords
{
    protected static string $resource = ComandaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
