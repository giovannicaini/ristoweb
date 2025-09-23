<?php

namespace App\Filament\Cassa\Widgets;

use App\Models\Cassa;
use App\Models\Evento;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Widgets\Widget;

class SelectCassaWidget extends Widget implements HasActions, HasForms

{
    use InteractsWithActions;
    use InteractsWithForms;

    protected string $view = 'filament.cassa.widgets.select-cassa-widget';

    public function testAction(): Action
    {
        return Action::make('testAction')
            ->modalHeading('Imposta Cassa e Evento')
            ->label('Imposta Cassa e Evento')
            //->requiresConfirmation()
            ->schema([
                Select::make('evento_id')
                    ->label("EVENTO")
                    ->options(Evento::orderBy('id', 'DESC')->get()->pluck('descrizione', 'id'))
                    ->columnSpan(1)
                    ->required()
                    ->live(),
                Select::make('cassa_id')
                    ->label("CASSA")
                    ->options(Cassa::orderBy('id', 'ASC')->pluck('nome', 'id'))
                    ->columnSpan(1)
                    ->required()
                    ->live()
            ])
            ->action(function ($data) {
                //dd($data);
                session(['cassa_corrente_id' => $data["cassa_id"]]);
                session(['evento_corrente_id' => $data["evento_id"]]);
            });
    }

    public function getEventoCorrente()
    {
        return Evento::find(Evento::Corrente());
    }

    public function getCassaCorrente()
    {
        return Cassa::find(Cassa::Corrente());
    }
}
