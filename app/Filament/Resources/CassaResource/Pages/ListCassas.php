<?php

namespace App\Filament\Resources\CassaResource\Pages;

use App\Filament\Resources\CassaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCassas extends ListRecords
{
    protected static string $resource = CassaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
