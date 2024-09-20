<?php

namespace App\Filament\Resources\TipologiaPagamentoResource\Pages;

use App\Filament\Resources\TipologiaPagamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTipologiaPagamento extends EditRecord
{
    protected static string $resource = TipologiaPagamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
