<?php

namespace App\Filament\Resources\PostazioneResource\Pages;

use App\Filament\Resources\PostazioneResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPostaziones extends ListRecords
{
    protected static string $resource = PostazioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
