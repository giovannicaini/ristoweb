<?php

namespace App\Actions;

use App\Helpers\PrintHelper;
use App\Models\Cassa;
use App\Models\Comanda;
use App\Models\ComandaDettaglio;
use App\Models\Postazione;
use App\Models\Stampante;
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
use Filament\Notifications\Notification;

class StampaScontrino
{
    use AsAction;
    private const secondi_attesa_stampante = 2;

    public function handle(Comanda $comanda, $tipo, $postazione_id = null, $messaggio = null)
    {
        $cassiere = User::find($comanda->cassiere_id);
        if (!$comanda)
            throw new \Exception("Comanda non trovata");
        $tipi = array("tutto", "scontrino-con-postazioni", "postazioni", "scontrino-senza-postazioni", "tutto_in_cassa_attiva", "postazione", "messaggio");
        if (!in_array($tipo, $tipi))
            throw new \Exception("Tipo stampa non valido");
        if (($tipo == "postazione" || $tipo == "messaggio") && !$postazione_id)
            throw new \Exception("Manca l'id postazione su cui stampare");
        if ($tipo == "messaggio" && !$messaggio)
            throw new \Exception("Manca il messaggio da stampare");
        //$postazioni_id = array();
        $postazioni_id = $comanda->comande_postazioni->pluck('postazione_id')->toArray();
        //$postazioni_scontrino = $comanda->postazioni->where('accoda_a_scontrino', true)->sortBy('ordine');
        $postazioni_scontrino = Postazione::whereIn('id', $postazioni_id)->where('accoda_a_scontrino', true)->orderBy('ordine')->get();
        foreach ($postazioni_scontrino as $p) {
            ////$dettaglip = ComandaDettaglio::where('comanda_id', $comanda->id)->whereIn('prodotto_id', Prodotto::whereIn('categoria_id', Categoria::where('postazione_id', $p->id)->get()->pluck('id')->toArray())->get()->pluck('id')->toArray())->get();
            $dettaglip = ComandaDettaglio::where('comanda_id', $comanda->id)->whereIn('prodotto_id', Prodotto::whereIn('categoria_id', $p->categorie->pluck('id')->toArray())->get()->pluck('id')->toArray())->get();
            $p->setAttribute('dettagli', $dettaglip);
        }
        //$postazioni_no_scontrino = $comanda->postazioni->where('accoda_a_scontrino', "!=", true)->sortBy('ordine');
        $postazioni_no_scontrino = Postazione::whereIn('id', $postazioni_id)->where('accoda_a_scontrino', "!=", true)->orderBy('ordine')->get();
        foreach ($postazioni_no_scontrino as $p) {
            $dettaglip = ComandaDettaglio::where('comanda_id', $comanda->id)->whereIn('prodotto_id', Prodotto::whereIn('categoria_id', $p->categorie->pluck('id')->toArray())->get()->pluck('id')->toArray())->get();
            $p->setAttribute('dettagli', $dettaglip);
        }
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
    }

    private function printScontrino(Comanda $comanda, $postazioni_scontrino, $stampa_cassa_attiva = false)
    {
        if (!$comanda)
            throw new ItemNotFoundException();
        if (!$comanda->cassa)
            throw new ItemNotFoundException();
        $stampante = $stampa_cassa_attiva ? Cassa::cassaCorrente()->stampante : $comanda->cassa->stampante;
        $stampante_coperti = Stampante::where('descrizione', 'STAMPANTE 4')->first();
        try {
            $connector = new NetworkPrintConnector($stampante->ip, 9100, $this::secondi_attesa_stampante);
            $printer = new Printer($connector);
            //$printer->setFont(Printer::FONT_B);
            $items = array();
            $subtotale = 0;
            $sconto = 0;
            foreach ($comanda->comande_dettagli as $dettaglio) {
                $subtotale += $dettaglio->prodotto->prezzo * $dettaglio->quantita;
                $sconto += $dettaglio->sconto_unitario * $dettaglio->quantita;
                $item = new Item($dettaglio->quantita, $dettaglio->prodotto->nome, $dettaglio->prodotto->prezzo * $dettaglio->quantita);
                array_push($items, $item);
            }
            $sconto += $comanda->sconto + $comanda->buoni;
            $item_subtotale = new Item('', 'Subtotale', $subtotale);
            $item_sconto = $sconto > 0 ? new Item('', 'Sconto', -$sconto) : null;

            /* Date is kept the same for testing */
            $dataor_attuale = date('d/m/Y H:i:s');

            /* Nome dell'evento */
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $tux = EscposImage::load($_SERVER['DOCUMENT_ROOT'] . "/images/logomedio.bmp", true);
            $printer->bitImage($tux);
            $printer->feed();
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
            $printer->feed();
            $printer->text("Pagamenti:");
            $printer->feed();
            if ($comanda->su_conto && $comanda->conto) {
                $su_conto_item = new Item("", "Su conto " . $comanda->conto->nome, $comanda->su_conto);
                $printer->text($su_conto_item->getAsString(48));
                $printer->feed();
            }
            foreach ($comanda->pagamenti as $pagamento) {
                $printer->text(new Item("", $pagamento->tipologia_pagamento->nome, $pagamento->importo));
                $printer->feed();
            }
            $printer->feed(2);

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text(date('d/m/Y H:i:s'));
            /* Cut the receipt and open the cash drawer */
            $printer->feed();

            if ($postazioni_scontrino != "[]") {
                foreach ($postazioni_scontrino as $p) {
                    $cp = ComandaPostazione::where('postazione_id', $p->id)->where('comanda_id', $comanda->id)->first();
                    $printer->feed();
                    $printer->feed();
                    $printer->cut();
                    //$printer->text(str_pad('', 48, "-"));
                    $this->printPostazione($comanda, $p, $printer, $cp);
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
            if ($comanda->numero_coperti() > 0) {
                try {
                    $connector = new NetworkPrintConnector($stampante_coperti->ip, 9100, $this::secondi_attesa_stampante);
                    $printer = new Printer($connector);
                    //$printer->setFont(Printer::FONT_B);
                    $printer->setJustification(Printer::JUSTIFY_CENTER);
                    $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
                    $printer->text(strtoupper("COPERTI"));
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
                    $coperti = $comanda->numero_coperti();
                    $printer->text($coperti . ' COPERTI ');
                    $printer->feed();
                    $printer->feed();
                    $printer->feed();
                    $printer->cut();
                    $printer->pulse();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Stampa Scontrino COPERTI non avvenuta')
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
            $comanda_postazione = $comanda->comande_postazioni->where('postazione_id', $p->id)->first();
            try {
                if ($comanda_postazione->printed) {
                    throw new \Exception("La comanda per la postazione $p->nome è già stata stampata. Per procedere ad una nuova stampa e annullare la precedente, andare in 'Stato Stampe Singole Postazioni'");
                }
                $connector = new NetworkPrintConnector($stampante->ip, 9100, $this::secondi_attesa_stampante);
                $printer = new Printer($connector);
                $this->printPostazione($comanda, $p, $printer, $comanda_postazione);
                $printer->cut();
                $comanda_postazione->printed_at = now();
                $comanda_postazione->save();
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

    private function printPostazione($comanda, $postazione, $printer, $comanda_postazione)
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
        //$printer->feed();
        //$printer->text("SCANSIONARE IL QR CODE PRIMA DI CONSEGNARE!");
        //$printer->feed();
        //$printer->qrCode($comanda_postazione->uuid, Printer::QR_ECLEVEL_H, 5);
        //$printer->feed();
        $printer->feed();
    }
}
