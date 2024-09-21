<?php

namespace App\Filament\Cassa\Pages;

use App\Models\Cassa;
use App\Models\Evento;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;

class Dashboard extends \Filament\Pages\Dashboard
{
    public $defaultAction = 'onboarding';

    public function onboardingAction(): Action
    {
        return Action::make('onboarding')
            ->modalHeading('Imposta Cassa e Evento')
            ->form([
                Select::make('evento_id')
                    ->label("EVENTO")
                    ->options(Evento::orderBy('id', 'DESC')->pluck('nome', 'id'))
                    ->default(Evento::Corrente())
                    ->columnSpan(1)
                    ->required()
                    ->live(),
                Select::make('cassa_id')
                    ->label("CASSA")
                    ->options(Cassa::orderBy('id', 'ASC')->pluck('nome', 'id'))
                    ->default(Cassa::Corrente())
                    ->columnSpan(1)
                    ->required()
                    ->live()
            ])
            ->visible(fn(): bool => !Cassa::Corrente() | !Evento::Corrente())
            ->action(function ($data) {
                //dd($data);
                session(['cassa_corrente_id' => $data["cassa_id"]]);
                session(['evento_corrente_id' => $data["evento_id"]]);
                header("Refresh:0");
            });
    }
}
