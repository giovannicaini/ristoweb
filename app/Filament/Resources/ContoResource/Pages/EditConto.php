<?php

namespace App\Filament\Resources\ContoResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use App\Filament\Resources\ContoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConto extends EditRecord
{
    protected static string $resource = ContoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
