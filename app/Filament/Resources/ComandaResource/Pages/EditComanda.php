<?php

namespace App\Filament\Resources\ComandaResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use App\Filament\Resources\ComandaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComanda extends EditRecord
{
    protected static string $resource = ComandaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
