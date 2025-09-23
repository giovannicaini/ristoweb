<?php

namespace App\Filament\Cassa\Resources\ComandaResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Cassa\Resources\ComandaResource;
use App\Models\Cassa;
use App\Models\Evento;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;

use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;

class ListComandas extends ListRecords
{
    protected static string $resource = ComandaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label("Crea Nuova Comanda [F3]")
                ->model(Comanda::class)
                ->schema([
                    TextInput::make('nominativo')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('tavolo')
                        ->maxLength(255),
                    Toggle::make('asporto'),
                ])
                ->keyBindings(["f3"])
                ->action(function (array $data): void {
                    $comanda = new \App\Models\Comanda();
                    $comanda->nominativo = $data["nominativo"];
                    $comanda->tavolo = $data["tavolo"];
                    $comanda->asporto = $data["asporto"];
                    $comanda->save();
                    redirect()->route('filament.cassa.resources.comandas.comanda', ['record' => $comanda]);
                })
        ];
    }

    public $defaultAction = 'onboarding';

    public function onboardingAction(): Action
    {
        return Action::make('onboarding')
            ->modalHeading('Imposta Cassa e Evento')
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
            ->visible(fn(): bool => !Cassa::Corrente() | !Evento::Corrente())
            ->action(function ($data) {
                //dd($data);
                session(['cassa_corrente_id' => $data["cassa_id"]]);
                session(['evento_corrente_id' => $data["evento_id"]]);
            });
    }
}
