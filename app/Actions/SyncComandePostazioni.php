<?php

namespace App\Actions;

use App\Helpers\PrintHelper;
use App\Models\Cassa;
use App\Models\Comanda;
use App\Models\ComandaDettaglio;
use App\Models\Postazione;
use App\Models\User;
use Illuminate\Support\ItemNotFoundException;
use Lorisleiva\Actions\Concerns\AsAction;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use App\Helpers\Item;
use App\Models\Categoria;
use App\Models\ComandaPostazione;
use App\Models\Prodotto;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class SyncComandePostazioni
{
    use AsAction;

    public function handle(Comanda $comanda)
    {
        try {
            if (!$comanda)
                throw new Exception("Comanda non trovata");

            $new_postazioni_ids = [];
            foreach ($comanda->comande_dettagli as $cd) {
                $postazione_id = $cd->prodotto->categoria->postazione_id;
                if (!in_array($postazione_id, $new_postazioni_ids))
                    $new_postazioni_ids[] = $postazione_id;
            }
            //$new_postazioni_ids = $comanda->comande_dettagli->prodotti->pluck('postazione_id')->unique()->toArray();
            $old_postazioni_ids = ComandaPostazione::where('comanda_id', $comanda->id)->pluck('postazione_id')->unique()->toArray();

            $add_postazioni = array_diff($new_postazioni_ids, $old_postazioni_ids);
            $remove_postazioni = array_diff($old_postazioni_ids, $new_postazioni_ids);
            $update_postazioni = array_diff($new_postazioni_ids, $old_postazioni_ids);

            //dd($add_postazioni);

            //$postazioni = Postazione::whereIn('id', $comanda->comande_dettagli->pluck('postazione_id')->unique()->toArray())->get();

            foreach ($add_postazioni as $postazione_id) {
                if ($postazione_id && !ComandaPostazione::where('postazione_id', $postazione_id)->where('comanda_id', $comanda->id)->first()) {
                    $cp = new ComandaPostazione;
                    $cp->uuid = (string)Str::uuid();
                    $cp->postazione_id = $postazione_id;
                    $cp->comanda_id = $comanda->id;
                    $cp->printed_at = null;
                    $cp->save();
                }
            }
            foreach ($remove_postazioni as $postazione_id) {
                $cp = ComandaPostazione::where('postazione_id', $postazione_id)->where('comanda_id', $comanda->id)->first();
                if ($cp)
                    $cp->delete();
            }
            foreach ($update_postazioni as $postazione_id) {
                $cp = ComandaPostazione::where('postazione_id', $postazione_id)->where('comanda_id', $comanda->id)->first();
                if ($cp && $cp->printed_at) {
                    Notification::make()
                        ->title('Sono stati aggiornati prodotti della postazione ' . $cp->postazione->nome . ' di cui era già stato stampato lo scontrino!')
                        ->body("Sarà necessario annullare lo scontrino già uscito manualmente, poi ristamparlo normalmente!")
                        ->danger()
                        ->persistent()
                        ->send();
                } else if ($cp) {
                    $cp->save();
                }
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Errore nella creazione delle comande per le postazioni')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }
}
