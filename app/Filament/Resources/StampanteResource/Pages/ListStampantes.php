<?php

namespace App\Filament\Resources\StampanteResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\StampanteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStampantes extends ListRecords
{
    protected static string $resource = StampanteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
