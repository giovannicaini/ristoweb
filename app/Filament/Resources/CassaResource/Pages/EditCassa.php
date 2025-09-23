<?php

namespace App\Filament\Resources\CassaResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use App\Filament\Resources\CassaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCassa extends EditRecord
{
    protected static string $resource = CassaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
