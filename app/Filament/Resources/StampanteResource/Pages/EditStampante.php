<?php

namespace App\Filament\Resources\StampanteResource\Pages;

use App\Filament\Resources\StampanteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStampante extends EditRecord
{
    protected static string $resource = StampanteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
