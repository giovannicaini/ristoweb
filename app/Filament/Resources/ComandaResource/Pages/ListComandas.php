<?php

namespace App\Filament\Resources\ComandaResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\ComandaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListComandas extends ListRecords
{
    protected static string $resource = ComandaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
