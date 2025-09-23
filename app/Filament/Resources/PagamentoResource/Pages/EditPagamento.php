<?php

namespace App\Filament\Resources\PagamentoResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\PagamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPagamento extends EditRecord
{
    protected static string $resource = PagamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
