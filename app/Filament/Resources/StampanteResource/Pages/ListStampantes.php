<?php

namespace App\Filament\Resources\StampanteResource\Pages;

use App\Filament\Resources\StampanteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStampantes extends ListRecords
{
    protected static string $resource = StampanteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
