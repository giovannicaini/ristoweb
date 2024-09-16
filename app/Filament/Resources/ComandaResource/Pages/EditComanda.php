<?php

namespace App\Filament\Resources\ComandaResource\Pages;

use App\Filament\Resources\ComandaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComanda extends EditRecord
{
    protected static string $resource = ComandaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
