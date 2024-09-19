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
use App\Helpers\Item;
use App\Models\Categoria;
use App\Models\Prodotto;
use Filament\Notifications\Notification;

class StampaScontrino
{
    use AsAction;

    public function handle(Comanda $comanda, $tipo)
    {
        $cassiere = User::find($comanda->cassiere_id);
        if (!$comanda)
            throw new ItemNotFoundException();
        $tipi = array("tutto", "scontrino-con-postazioni", "postazioni", "scontrino-senza-postazioni", "tutto_in_cassa_attiva");
        if (!in_array($tipo, $tipi))
            throw new ItemNotFoundException();
        $postazioni_id = array();
        foreach ($comanda->comande_dettagli as $dettaglio) {
            $postazione = $dettaglio->prodotto->categoria->postazione_id;
            if (!in_array($postazione, $postazioni_id))
                array_push($postazioni_id, $postazione);
        }
        error_log("1");
        $postazioni_scontrino = Postazione::whereIn('id', $postazioni_id)->where('accoda_a_scontrino', true)->orderBy('ordine')->get();
        foreach ($postazioni_scontrino as $p) {
            ////$dettaglip = ComandaDettaglio::where('comanda_id', $comanda->id)->whereIn('prodotto_id', Prodotto::whereIn('categoria_id', Categoria::where('postazione_id', $p->id)->get()->pluck('id')->toArray())->get()->pluck('id')->toArray())->get();
            $dettaglip = ComandaDettaglio::where('comanda_id', $comanda->id)->whereIn('prodotto_id', Prodotto::whereIn('categoria_id', $p->categorie->pluck('id')->toArray())->get()->pluck('id')->toArray())->get();
            $p->setAttribute('dettagli', $dettaglip);
        }
        error_log("2");
        $postazioni_no_scontrino = Postazione::whereIn('id', $postazioni_id)->where('accoda_a_scontrino', null)->orderBy('ordine')->get();
        foreach ($postazioni_no_scontrino as $p) {
            $dettaglip = ComandaDettaglio::where('comanda_id', $comanda->id)->whereIn('prodotto_id', Prodotto::whereIn('categoria_id', Categoria::where('postazione_id', $p->id)->get()->pluck('id')->toArray())->get()->pluck('id')->toArray())->get();
            $p->setAttribute('dettagli', $dettaglip);
        }
        error_log("3");
        if ($tipo == "tutto" || $tipo == "scontrino-con-postazioni")
            $this->printScontrino($comanda, $postazioni_scontrino);
        elseif ($tipo == "scontrino-senza-postazioni")
            $this->printScontrino($comanda, []);
        if ($tipo == "tutto" || $tipo == "postazioni")
            $this->printPostazioni($comanda, $postazioni_no_scontrino);
        if ($tipo == "tutto_in_cassa_attiva") {
            $this->printScontrino($comanda, $postazioni_scontrino, true);
            $this->printPostazioni($comanda, $postazioni_no_scontrino, true);
        }
        error_log("4");
    }

    private function printScontrino(Comanda $comanda, $postazioni_scontrino, $stampa_cassa_attiva = false)
    {
        if (!$comanda)
            throw new ItemNotFoundException();
        if (!$comanda->cassa)
            throw new ItemNotFoundException();
        $stampante = $stampa_cassa_attiva ? Cassa::cassaCorrente()->stampante : $comanda->cassa->stampante;
        try {
            $connector = new NetworkPrintConnector($stampante->ip, 9100, 1);
            $printer = new Printer($connector);
            //$printer->setFont(Printer::FONT_B);
            $items = array();
            $subtotale = 0;
            $sconto = 0;
            foreach ($comanda->dettagli as $dettaglio) {
                $subtotale += $dettaglio->prodotto->prezzo * $dettaglio->quantita;
                $sconto += $dettaglio->sconto_unitario * $dettaglio->quantita;
                $item = new Item($dettaglio->quantita, $dettaglio->prodotto->nome, $dettaglio->prodotto->prezzo * $dettaglio->quantita);
                array_push($items, $item);
            }
            $item_subtotale = new Item('', 'Subtotale', $subtotale);
            $item_sconto = $sconto > 0 ? new Item('', 'Subtotale', $subtotale) : null;

            /* Date is kept the same for testing */
            $dataor_attuale = date('d/m/Y H:i:s');

            /* Nome dell'evento */
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->text($comanda->evento->nome . ' - ' . date("d/m/Y", strtotime($comanda->evento->data)));
            $printer->setEmphasis(false);
            $printer->feed();

            /* Numero della comanda */
            $printer->setEmphasis(true);
            $printer->text("COMANDA N." . $comanda->n_ordine . ' - [' . $comanda->nominativo . ']');
            $printer->setEmphasis(false);
            $printer->feed();

            /* Items */
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->setEmphasis(true);
            $printer->textRaw(chr($stampante->codice_euro));
            $printer->setEmphasis(false);
            $printer->feed();
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            foreach ($items as $item) {
                $printer->text($item->getAsString(48)); // for 58mm Font A
            }
            $printer->setEmphasis(true);
            $printer->text($item_subtotale->getAsString(48));
            $printer->setEmphasis(false);
            $printer->feed();
            if ($item_sconto) {
                $printer->setEmphasis(true);
                $printer->text($item_sconto->getAsString(48));
                $printer->setEmphasis(false);
                $printer->feed();
            }
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer->text("Totale          ");
            $printer->textRaw(chr($stampante->codice_euro));
            $printer->text(str_pad(number_format($subtotale - $sconto, 2), 7, ' ', STR_PAD_LEFT));
            $printer->selectPrintMode();
            $printer->feed(2);

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text(date('d/m/Y H:i:s'));
            /* Cut the receipt and open the cash drawer */
            $printer->feed();

            if ($postazioni_scontrino != "[]") {
                foreach ($postazioni_scontrino as $p) {
                    $printer->feed();
                    $printer->feed();
                    $printer->text(str_pad('', 48, "-"));
                    $this->printPostazione($comanda, $p, $printer);
                }
            }

            $printer->cut();
            $printer->pulse();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Stampa Scontrino ' . $comanda->cassa->nome . ' (' . $comanda->cassa->stampante->descrizione . ') non avvenuta')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        } finally {
            if (isset($printer))
                $printer->close();
        }
    }

    private function printPostazioni($comanda, $postazioni_no_scontrino, $stampa_cassa_attiva = false)
    {
        if (!$comanda)
            throw new ItemNotFoundException();
        if (!$comanda->cassa)
            throw new ItemNotFoundException();
        //$stampante = Stampante::find($cassa->stampante_id);

        foreach ($postazioni_no_scontrino as $p) {
            $stampante = $stampa_cassa_attiva ? Cassa::cassaCorrente()->stampante : ($p->stampante ?? $comanda->cassa->stampante);
            try {
                $connector = new NetworkPrintConnector($stampante->ip, 9100, 1);
                $printer = new Printer($connector);
                $this->printPostazione($comanda, $p, $printer);
                $printer->cut();
            } catch (\Exception $e) {
                Notification::make()
                    ->title('Stampa Postazione ' . $p->nome . ' (' . $stampante->descrizione . ') non avvenuta')
                    ->body($e->getMessage())
                    ->danger()
                    ->persistent()
                    ->send();
            } finally {
                if (isset($printer))
                    $printer->close();
            }
        }
    }

    private function printPostazione($comanda, $postazione, $printer)
    {
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
        $printer->text(strtoupper($postazione->nome));
        $printer->feed();
        $printer->text("Comanda N. " . $comanda->n_ordine);
        $printer->selectPrintMode();
        $printer->feed();
        if ($comanda->tavolo) {
            $printer->text('Tavolo ' . $comanda->tavolo);
            $printer->feed();
        }
        if ($comanda->asporto) {
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer->text('ASPORTO');
            $printer->selectPrintMode();
            $printer->feed();
        }
        $printer->text('[' . $comanda->nominativo . ']');
        $printer->feed(2);
        $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
        $printer->setJustification(Printer::JUSTIFY_LEFT);

        foreach ($postazione->dettagli as $dettaglio) {
            $printer->text($dettaglio->quantita . ' x ' . $dettaglio->prodotto->nome_breve);
            $printer->feed();
            if ($dettaglio->note) {
                $printer->selectPrintMode();
                $printer->text("--NOTE: " . $dettaglio->note);
                $printer->feed();
                $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            }
        }
        if ($postazione->stampa_coperti) {
            $coperti = $comanda->numero_coperti();
            $printer->text($coperti . ' COPERTI ');
            $printer->feed();
        }
        $printer->feed();
        $printer->selectPrintMode();
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text(date('d/m/Y H:i:s'));
        $printer->feed();
        $printer->feed();
        $printer->feed();
    }
}
