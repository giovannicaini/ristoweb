<?php

namespace App\Filament\Cassa\Loggers;

use App\Models\Comanda;
use App\Filament\Cassa\Resources\ComandaResource;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\RelationManager;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class ComandaLogger extends Logger
{
    public static ?string $model = Comanda::class;

    public static function getLabel(): string | Htmlable | null
    {
        return ComandaResource::getModelLabel();
    }

    public static function resource(ResourceLogger $logger): ResourceLogger
    {
        return $logger
            ->fields([
                Field::make('id')
                    ->label(__('ID'))
                    ->badge(),
                Field::make('tavolo_id')
                    ->label(__('Tavolo'))
                    ->badge(),
            ])
            ->relationManagers([
                //
            ]);
    }
}
