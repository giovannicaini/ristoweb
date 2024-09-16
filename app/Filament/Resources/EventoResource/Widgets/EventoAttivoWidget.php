<?php

namespace App\Filament\Resources\EventoResource\Widgets;

use App\Models\Evento;
use Filament\Widgets\Widget;

class EventoAttivoWidget extends Widget
{
    protected static string $view = 'filament.resources.evento-resource.widgets.evento-attivo-widget';

    protected function getViewData(): array
    {
        return [
            "evento_attivo" => Evento::where('attivo', 1)->first()
        ];
    }
}
