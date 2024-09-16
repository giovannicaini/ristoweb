<?php

namespace App\Filament\Resources\CassaResource\Pages;

use App\Filament\Resources\CassaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCassa extends EditRecord
{
    protected static string $resource = CassaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
