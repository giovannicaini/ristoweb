<?php

namespace App\Filament\Resources\ContoResource\Pages;

use App\Filament\Resources\ContoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContos extends ListRecords
{
    protected static string $resource = ContoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
