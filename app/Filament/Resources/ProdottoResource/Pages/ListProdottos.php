<?php

namespace App\Filament\Resources\ProdottoResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\ProdottoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProdottos extends ListRecords
{
    protected static string $resource = ProdottoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
