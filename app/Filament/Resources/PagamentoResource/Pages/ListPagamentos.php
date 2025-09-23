<?php

namespace App\Filament\Resources\PagamentoResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\PagamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPagamentos extends ListRecords
{
    protected static string $resource = PagamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
