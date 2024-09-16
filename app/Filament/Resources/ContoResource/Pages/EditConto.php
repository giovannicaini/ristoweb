<?php

namespace App\Filament\Resources\ContoResource\Pages;

use App\Filament\Resources\ContoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConto extends EditRecord
{
    protected static string $resource = ContoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
