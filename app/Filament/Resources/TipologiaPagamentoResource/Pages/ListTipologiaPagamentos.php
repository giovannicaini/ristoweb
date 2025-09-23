<?php

namespace App\Filament\Resources\TipologiaPagamentoResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\TipologiaPagamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTipologiaPagamentos extends ListRecords
{
    protected static string $resource = TipologiaPagamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
