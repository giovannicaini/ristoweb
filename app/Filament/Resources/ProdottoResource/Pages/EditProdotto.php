<?php

namespace App\Filament\Resources\ProdottoResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use App\Filament\Resources\ProdottoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProdotto extends EditRecord
{
    protected static string $resource = ProdottoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
