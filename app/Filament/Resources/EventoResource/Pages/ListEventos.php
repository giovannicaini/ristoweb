<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use App\Models\Evento;
use App\Models\Postazione;
use App\Models\Scopes\EventoScope;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListEventos extends ListRecords
{
    protected static string $resource = EventoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('importaDatiEventoAttivoDaAltroEvento')
                ->form([
                    TextInput::make('evento_id')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $new_evento = Evento::where('attivo', true)->first();
                    $old_evento_id = $data["evento_id"];
                    $old_evento = Evento::find($old_evento_id);
                    if (!$old_evento) {
                        Notification::make()
                            ->title('Evento non trovato!')
                            ->danger()
                            ->send();
                        return;
                    }
                    $check = Postazione::first();
                    if ($check) {
                        Notification::make()
                            ->title('Esistono già postazioni! Impossibile importare!')
                            ->danger()
                            ->send();
                        return;
                    }
                    $old_postazioni = Postazione::withoutGlobalScope(EventoScope::class)->where('evento_id', $old_evento->id)->get();
                    foreach ($old_postazioni as $old_postazione) {
                        $new_postazione = $old_postazione->replicate();
                        $new_postazione->evento_id = $new_evento->id;
                        $new_postazione->save();
                        foreach ($old_postazione->categorieNS as $old_categoria) {
                            $new_categoria = $old_categoria->replicate();
                            $new_categoria->evento_id = $new_evento->id;
                            $new_categoria->postazione_id = $new_postazione->id;
                            $new_categoria->save();
                            foreach ($old_categoria->prodottiNS as $old_prodotto) {
                                $new_prodotto = $old_prodotto->replicate();
                                $new_prodotto->evento_id = $new_evento->id;
                                $new_prodotto->categoria_id = $new_categoria->id;
                                $new_prodotto->save();
                            }
                        }
                    }
                    Notification::make()
                        ->title('Importazione avvenuta correttamente!')
                        ->success()
                        ->send();



                    error_log(json_encode($old_postazioni));
                })
        ];
    }
}
