<?php

/*
 * FermiOnLine - Liceo Fermi Bologna - https://web.liceofermibo.edu.it
 * by Giovanni Caini
 *
 * GESTIONE MODULI
 * Controller class for /moduli URLs.
 */

namespace App\Http\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use UserFrosting\Fortress\Adapter\JqueryValidationAdapter;
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\ServerSideValidator;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Support\Exception\NotFoundException;
use UserFrosting\Support\Exception\ForbiddenException;
use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Account\Database\Models\User;

use App\Models\Categoria;
use App\Models\Comanda;
use App\Models\ComandaDettaglio;
use App\Models\Conto;
use App\Models\Evento;
use App\Models\Postazione;
use App\Models\Prodotto;
use App\Models\Cassa;
use App\Models\Stampante;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\CapabilityProfile;


use Dompdf\Dompdf;
use Dompdf\Options;

class RistoController extends BaseController
{
	use AuthorizesRequests, ValidatesRequests;

	private function eventoCorrente()
	{
		return Evento::where('attivo', true)->first();
	}

	private function cassaCorrente()
	{
		$cassa = Cassa::find($_SESSION["cassa_id"]);
		if ($cassa)
			return $cassa;
		else
			return false;
	}

	public function pageComande(Request $request, Response $response, array $args): Response
	{
		$params = $request->getQueryParams();
		$currentUser = $this->ci->currentUser;
		//$comande = Comanda::orderBy('n_ordine','DESC')->where('evento_id',$this->eventoCorrente()->id)->where('created_at','>=',"2022-09-25")->get();
		$comande = Comanda::orderBy('n_ordine', 'DESC')->where('evento_id', $this->eventoCorrente()->id)->get();
		foreach ($comande as $comanda) {
			$comanda->setAttribute('cassiere', User::find($comanda->cassiere_id));
		}

		return $this->ci->view->render($response, 'pages/comande.html.twig', [
			'evento' => $this->eventoCorrente(),
			'cassa' => $this->cassaCorrente(),
			'comande' => $comande,
			'utente' => $currentUser
		]);
	}

	public function pageQr()
	{
		return View('pages.comanda', []);
	}

	public function pageComanda(Request $request, Response $response, array $args): Response
	{
		$params = $request->getQueryParams();
		$currentUser = $this->ci->currentUser;
		$comanda = Comanda::find($args["comanda_id"]);
		$cassiere = User::find($comanda->cassiere_id);
		$cassa = Cassa::find($comanda->cassa_id);
		if (!$comanda)
			throw new NotFoundException();

		$categorie1 = Categoria::where('evento_id', $comanda->evento_id)->where('colonna', 1)->orderBy('ordine')->get();
		foreach ($categorie1 as $categoria) {
			$prodotti = Prodotto::where('categoria_id', $categoria->id)->where('attivo', true)->orderBy('ordine')->get();
			foreach ($prodotti as $prodotto) {
				$dettaglio = ComandaDettaglio::where('comanda_id', $comanda->id)->where('prodotto_id', $prodotto->id)->first();
				if ($dettaglio) {
					$prodotto->setAttribute('quantita', $dettaglio->quantita);
					$prodotto->setAttribute('note', $dettaglio->note);
				}
			}
			$categoria->setAttribute('prodotti', $prodotti);
		}
		$categorie2 = Categoria::where('evento_id', $comanda->evento_id)->where('colonna', 2)->orderBy('ordine')->get();
		foreach ($categorie2 as $categoria) {
			$prodotti = Prodotto::where('categoria_id', $categoria->id)->where('attivo', true)->orderBy('ordine')->get();
			foreach ($prodotti as $prodotto) {
				$dettaglio = ComandaDettaglio::where('comanda_id', $comanda->id)->where('prodotto_id', $prodotto->id)->first();
				if ($dettaglio) {
					$prodotto->setAttribute('quantita', $dettaglio->quantita);
					$prodotto->setAttribute('note', $dettaglio->note);
				}
			}
			$categoria->setAttribute('prodotti', $prodotti);
		}

		$dettagli = ComandaDettaglio::where('comanda_id', $comanda->id)->get();
		foreach ($dettagli as $dettaglio) {
			$dettaglio->setAttribute('prodotto', Prodotto::find($dettaglio->prodotto_id));
		}

		$conti = Conto::where('evento_id', $this->eventoCorrente()->id)->orderBy('nome')->get();

		return $this->ci->view->render($response, 'pages/comanda.html.twig', [
			'cassiere' => $cassiere,
			'cassa' => $cassa,
			'evento' => $this->eventoCorrente(),
			'conti' => $conti,
			'comanda' => $comanda,
			'dettagli' => $dettagli,
			'categorie1' => $categorie1,
			'categorie2' => $categorie2,
			'utente' => $currentUser
		]);
	}


	public function modalEliminaComanda(Request $request, Response $response, array $args): Response
	{
		$params = $request->getQueryParams();
		$currentUser = $this->ci->currentUser;

		$comanda = Comanda::find($args["comanda_id"]);
		if (!$comanda) {
			$this->ci->alerts->addMessageTranslated('danger', 'Comanda non trovata!');
			return $response->withJson([], 503);
		}

		return $this->ci->view->render($response, 'modals/confirm-delete-comanda.html.twig', [
			'utente' => $currentUser,
			'comanda' => $comanda,
			'form'    => [
				'action'      => 'api/comande/elimina-comanda/' . $comanda->id,
				'method'      => 'DELETE',
				'submit_text' => 'Elimina Comanda'
			]
		]);
	}

	public function modalCambiaEventoCorrente(Request $request, Response $response, array $args): Response
	{
		$params = $request->getQueryParams();
		$currentUser = $this->ci->currentUser;

		return $this->ci->view->render($response, 'modals/cambia-evento-corrente.html.twig', [
			'utente' => $currentUser,
			'eventi' => Evento::get(),
			'form'    => [
				'action'      => 'api/comande/cambia-evento-corrente',
				'method'      => 'POST',
				'submit_text' => 'Cambia Evento Corrente'
			]
		]);
	}

	public function modalCambiaCassaCorrente(Request $request, Response $response, array $args): Response
	{
		$params = $request->getQueryParams();
		$currentUser = $this->ci->currentUser;
		$cassa_corrente = $this->cassaCorrente();
		return $this->ci->view->render($response, 'modals/cambia-cassa-corrente.html.twig', [
			'utente' => $currentUser,
			'casse' => Cassa::get(),
			'cassa_corrente_id' => $cassa_corrente->id,
			'form'    => [
				'action'      => 'api/comande/cambia-cassa-corrente',
				'method'      => 'POST',
				'submit_text' => 'Cambia Cassa Corrente'
			]
		]);
	}
	public function modalInviaMessaggioCassa(Request $request, Response $response, array $args): Response
	{
		$params = $request->getQueryParams();
		$currentUser = $this->ci->currentUser;
		$cassa_corrente = $this->cassaCorrente();
		return $this->ci->view->render($response, 'modals/invia-messaggio-cassa.html.twig', [
			'utente' => $currentUser,
			'casse' => Cassa::get(),
			'cassa_corrente_id' => $cassa_corrente->id,
			'form'    => [
				'action'      => 'api/comande/invia-messaggio-cassa',
				'method'      => 'POST',
				'submit_text' => 'Invia Messaggio'
			]
		]);
	}

	public function apiEliminaComanda($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$currentUser = $this->ci->currentUser;

		$comanda = Comanda::find($args["comanda_id"]);
		if (!$comanda) {
			$this->ci->alerts->addMessageTranslated('danger', 'Comanda non trovata!');
			return $response->withJson([], 503);
		}

		Capsule::transaction(function () use ($comanda, $currentUser) {

			$comanda_dettaglio = ComandaDettaglio::where('comanda_id', $comanda->id)->get();
			foreach ($comanda_dettaglio as $cd) {
				$cd->delete();
			}
			$comanda->delete();
			$messaggio = 'Comanda eliminata!';
			$this->ci->alerts->addMessageTranslated('danger', $messaggio);
			$this->ci->userActivityLogger->info("Eliminata comanda " . $comanda->id, [
				'type'    => 'comanda_eliminata',
				'user_id' => $currentUser->id,
			]);
		});
		return $response->withJson([$id], 200);
	}

	public function apiCambiaEventoCorrente($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$currentUser = $this->ci->currentUser;

		$evento = Evento::find($params["evento"]);
		if (!$evento) {
			$this->ci->alerts->addMessageTranslated('danger', 'Evento non trovato!');
			return $response->withJson([], 503);
		}

		Capsule::transaction(function () use ($evento, $currentUser) {

			$eventi = Evento::get();
			foreach ($eventi as $e) {
				if ($e->id == $evento->id)
					$e->attivo = true;
				else
					$e->attivo = null;
				$e->save();
			}
			$messaggio = 'Selezionato evento ' . $evento->nome . ' - ' . date("d/m/Y", strtotime($evento->data));
			$this->ci->alerts->addMessageTranslated('success', $messaggio);
			$this->ci->userActivityLogger->info("Selezionato evento " . $evento->id, [
				'type'    => 'evento_selezionato',
				'user_id' => $currentUser->id,
			]);
		});
		return $response->withJson([$id], 200);
	}

	public function apiCambiaCassaCorrente($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$currentUser = $this->ci->currentUser;

		$cassa = Cassa::find($params["cassa"]);
		if (!$cassa) {
			$this->ci->alerts->addMessageTranslated('danger', 'Cassa non trovata!');
			return $response->withJson([], 503);
		}
		$messaggio = 'Selezionata ' . $cassa->nome;
		$this->ci->alerts->addMessageTranslated('success', $messaggio);
		$_SESSION["cassa_id"] = $cassa->id;
		return $response->withJson([$id], 200);
	}

	public function apiInviaMessaggioCassa($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$currentUser = $this->ci->currentUser;

		$cassa = Cassa::find($params["cassa"]);
		if (!$cassa) {
			$this->ci->alerts->addMessageTranslated('danger', 'Cassa non trovata!');
			return $response->withJson([], 503);
		}
		$stampante = Stampante::find($cassa->stampante_id);

		$connector = new NetworkPrintConnector($stampante->ip, 9100);

		$messaggio_da = $params["messaggio_da"];
		$messaggio = $params["messaggio"];

		try {
			$printer = new Printer($connector);
			/* Nome dell'evento */
			$printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
			$printer->setJustification(Printer::JUSTIFY_CENTER);
			$printer->setEmphasis(true);
			$printer->feed(3);
			$printer->text("MESSAGGIO DA: " . $messaggio_da);
			$printer->setJustification(Printer::JUSTIFY_LEFT);
			$printer->feed();
			$printer->feed();
			$printer->text($messaggio);
			$printer->feed(3);
			$printer->cut();
			$printer->pulse();
		} catch (Exception $e) {
			echo $e->getMessage();
		} finally {
			$printer->close();
		}
		return $response->withJson([$id], 200);
	}

	public function modalNuovaComanda(Request $request, Response $response, array $args): Response
	{
		$params = $request->getQueryParams();
		$currentUser = $this->ci->currentUser;
		if (!$this->cassaCorrente()) {
			$messaggio = 'Per aggiungere comande è necessario selezionare prima la Cassa da utilizzare!! (Usare il pulsante arancione "Cambia Cassa Corrente")';
			//$this->ci->alerts->addMessageTranslated('danger', $messaggio);
			return $response->withJson([$id], 404);
		}
		return $this->ci->view->render($response, 'modals/nuova-comanda.html.twig', [
			'utente' => $currentUser,
			'evento' => $this->eventoCorrente(),
			'cassa' => $this->cassaCorrente(),
			'form'    => [
				'action'      => 'api/comande/nuova-comanda',
				'method'      => 'POST',
				'submit_text' => 'Crea Comanda'
			]
		]);
		return $this->ci->view->render($response, 'modals/nuova-comanda.html.twig', [
			'utente' => $currentUser
		]);
	}

	public function apiNuovaComanda($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$currentUser = $this->ci->currentUser;

		$nominativo = $params["nominativo"];
		if (!$nominativo) {
			$this->ci->alerts->addMessageTranslated('danger', 'Devi inserire il nominativo!');
			return $response->withJson([], 503);
		}

		$evento = Evento::find($params["evento_id"]);
		if (!$evento) {
			$this->ci->alerts->addMessageTranslated('danger', 'Evento non trovato!');
			return $response->withJson([], 503);
		}

		$cassa = Cassa::find($params["cassa_id"]);
		if (!$cassa) {
			$this->ci->alerts->addMessageTranslated('danger', 'Cassa non trovata!');
			return $response->withJson([], 503);
		}

		$cassiere_id = $params["cassiere_id"];
		if (!$cassiere_id) {
			$this->ci->alerts->addMessageTranslated('danger', 'Cassiere non trovato!');
			return $response->withJson([], 503);
		}

		if ($params["asporto"])
			$asporto = true;
		else
			$asporto = null;

		$last_comanda = Comanda::where('evento_id', $evento->id)->orderBy('n_ordine', 'DESC')->first();
		if (!$last_comanda)
			$n_ordine = 1;
		else
			$n_ordine = $last_comanda->n_ordine + 1;
		$id = null;
		Capsule::transaction(function () use (&$id, $nominativo, $evento, $cassiere_id, $cassa, $asporto, $n_ordine, $currentUser) {

			$comanda = new Comanda();
			$comanda->nominativo = $nominativo;
			$comanda->evento_id = $evento->id;
			$comanda->n_ordine = $n_ordine;
			$comanda->cassiere_id = $cassiere_id;
			$comanda->cassa_id = $cassa->id;
			$comanda->asporto = $asporto;
			$comanda->stato = "aperta";

			$comanda->save();
			$id = $comanda->id;
			$messaggio = 'Comanda ' . $comanda->id . ' Creata - Numero Ordine: ' . $n_ordine;
			//$this->ci->alerts->addMessageTranslated('success', $messaggio);
			$this->ci->userActivityLogger->info("Creazione comanda " . $comanda->id, [
				'type'    => 'comanda_creazione',
				'user_id' => $currentUser->id,
			]);
		});
		return $response->withJson([$id], 200);
	}

	public function apiCompilaComanda($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$currentUser = $this->ci->currentUser;

		$comanda = Comanda::find($args["comanda_id"]);
		if (!$comanda) {
			$this->ci->alerts->addMessageTranslated('danger', 'Comanda non trovata!');
			return $response->withJson([], 503);
		}

		$ordini = json_decode($params["ordine"]);
		foreach ($ordini as $ordine) {
			$prodotto = Prodotto::find($ordine->prodotto_id);
			if (!$prodotto) {
				$this->ci->alerts->addMessageTranslated('danger', 'Ci sono prodotti non validi!');
				return $response->withJson([], 503);
			}
		}

		Capsule::transaction(function () use ($comanda, $ordini, $currentUser) {

			$dettagli_old = ComandaDettaglio::where('comanda_id', $comanda->id)->get();
			foreach ($dettagli_old as $d) {
				$d->delete();
			}

			$totale = 0;
			foreach ($ordini as $ordine) {
				$prodotto = Prodotto::find($ordine->prodotto_id);
				$dettaglio = new ComandaDettaglio();
				$dettaglio->comanda_id = $comanda->id;
				$dettaglio->prodotto_id = $ordine->prodotto_id;
				$dettaglio->quantita = $ordine->quantita;
				if ($ordine->note)
					$dettaglio->note = $ordine->note;
				$dettaglio->save();
				$totale += $dettaglio->quantita * $prodotto->prezzo;
			}
			$comanda->totale = $totale;
			$comanda->stato = 'compilata';
			$comanda->save();
			$messaggio = 'Comanda ' . $comanda->id . ' compilata - Numero Ordine: ' . $n_ordine;
			//$this->ci->alerts->addMessageTranslated('success', $messaggio);
			$this->ci->userActivityLogger->info("Compilazione comanda " . $comanda->id, [
				'type'    => 'comanda_compilazione',
				'user_id' => $currentUser->id,
			]);
		});
		return $response->withJson([], 200);
	}


	public function apiInviaComanda($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$currentUser = $this->ci->currentUser;

		$comanda = Comanda::find($args["comanda_id"]);
		if (!$comanda) {
			$this->ci->alerts->addMessageTranslated('danger', 'Comanda non trovata!');
			return $response->withJson([], 503);
		}
		if ($params["sconto"])
			$sconto = $params["sconto"];

		if ($params["buoni"])
			$buoni = $params["buoni"];

		if ($params["pagato"])
			$pagato = $params["pagato"];

		if ($params["su_conto"])
			$su_conto = $params["su_conto"];

		if ($params["conto"])
			$conto = $params["conto"];

		Capsule::transaction(function () use ($comanda, $sconto, $buoni, $pagato, $conto, $su_conto, $currentUser) {

			if ($conto) {
				$conto_ok = Conto::where('id', $conto)->where('evento_id', $this->eventoCorrente()->id)->first();

				if (!$conto_ok) {
					$conto_ok = new Conto();
					$conto_ok->nome = $conto;
					$conto_ok->evento_id = $this->eventoCorrente()->id;
					$conto_ok->save();
				}
			}
			$comanda->sconto = $sconto;
			$comanda->buoni = $buoni;
			$comanda->pagato = $pagato;
			$comanda->conto_id = $conto_ok->id;
			$comanda->su_conto = $su_conto;
			$comanda->stato = 'pagata';
			$comanda->save();
			$messaggio = 'Comanda ' . $comanda->id . ' pagata - Numero Ordine: ' . $comanda->n_ordine;
			//$this->ci->alerts->addMessageTranslated('success', $messaggio);
			$this->ci->userActivityLogger->info("Pagamento comanda " . $comanda->id, [
				'type'    => 'comanda_pagata',
				'user_id' => $currentUser->id,
			]);
		});
		return $response->withJson([], 200);
	}

	public function apiAggiornaComanda($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$currentUser = $this->ci->currentUser;

		$comanda = Comanda::find($args["comanda_id"]);
		if (!$comanda) {
			//$this->ci->alerts->addMessageTranslated('danger', 'Comanda non trovata!');
			return $response->withJson([], 503);
		}
		$campi = ["nominativo", "tavolo", "asporto", "note"];
		$campo = $args["campo"];
		if (!in_array($campo, $campi)) {
			//$this->ci->alerts->addMessageTranslated('danger', 'Campo non trovato!');
			return $response->withJson([], 503);
		}
		$valore = $params["valore"];
		Capsule::transaction(function () use ($campo, $comanda, $valore, $currentUser) {
			if ($campo == "nominativo")
				$comanda->nominativo = $valore;
			if ($campo == "tavolo")
				$comanda->tavolo = $valore;
			if ($campo == "asporto")
				$comanda->asporto = $valore;
			if ($campo == "note")
				$comanda->note = $valore;
			$comanda->save();
			$messaggio = 'Comanda ' . $comanda->id . ' aggiornata - Campo ' . $campo;
			//$this->ci->alerts->addMessageTranslated('success', $messaggio);
			$this->ci->userActivityLogger->info("Aggiornamento comanda " . $comanda->id, [
				'type'    => 'comanda_aggiornata',
				'user_id' => $currentUser->id,
			]);
		});
		return $response->withJson([], 200);
	}


	public function pdfComanda(Request $request, Response $response, array $args)
	{
		$params = $request->getQueryParams();
		$currentUser = $this->ci->currentUser;
		$comanda = Comanda::find($args["comanda_id"]);
		$cassiere = User::find($comanda->cassiere_id);
		if (!$comanda)
			throw new NotFoundException();
		$postazioni_id = array();
		$dettagli = ComandaDettaglio::where('comanda_id', $comanda->id)->get();
		foreach ($dettagli as $dettaglio) {
			$dettaglio->setAttribute('prodotto', Prodotto::find($dettaglio->prodotto_id));
			$postazione = Categoria::find($dettaglio->prodotto->categoria_id)->postazione_id;
			if (!in_array($postazione, $postazioni_id))
				array_push($postazioni_id, $postazione);
		}

		$postazioni_scontrino = Postazione::whereIn('id', $postazioni_id)->where('accoda_a_scontrino', true)->where('evento_id', $comanda->evento_id)->orderBy('ordine')->get();
		foreach ($postazioni_scontrino as $p) {
			$dettaglip = ComandaDettaglio::where('comanda_id', $comanda->id)->whereIn('prodotto_id', Prodotto::whereIn('categoria_id', Categoria::where('postazione_id', $p->id)->get()->pluck('id')->toArray())->get()->pluck('id')->toArray())->get();
			foreach ($dettaglip as $dettaglio) {
				$dettaglio->setAttribute('prodotto', Prodotto::find($dettaglio->prodotto_id));
			}
			$p->setAttribute('dettagli', $dettaglip);
		}
		$postazioni_no_scontrino = Postazione::whereIn('id', $postazioni_id)->where('accoda_a_scontrino', null)->where('evento_id', $comanda->evento_id)->orderBy('ordine')->get();
		foreach ($postazioni_no_scontrino as $p) {
			$dettaglip = ComandaDettaglio::where('comanda_id', $comanda->id)->whereIn('prodotto_id', Prodotto::whereIn('categoria_id', Categoria::where('postazione_id', $p->id)->get()->pluck('id')->toArray())->get()->pluck('id')->toArray())->get();
			foreach ($dettaglip as $dettaglio) {
				$dettaglio->setAttribute('prodotto', Prodotto::find($dettaglio->prodotto_id));
			}
			$p->setAttribute('dettagli', $dettaglip);
		}

		$html = $this->ci->view->fetch('pdf/comanda.html.twig', [
			'cassiere' => $cassiere,
			'evento' => $this->eventoCorrente(),
			'comanda' => $comanda,
			'dettagli' => $dettagli,
			'postazioni_scontrino' => $postazioni_scontrino,
			'postazioni_no_scontrino' => $postazioni_no_scontrino,
			'utente' => $currentUser
		]);

		//$loader = new \Twig\Loader\FilesystemLoader('/srv/web/app/sprinkles/elezioni/templates');
		//$twig = new \Twig\Environment($loader, []);
		//$html = $response->getBody();
		//$html = "<h1>Ciao</h1>";

		$options = new Options();
		$options->set('isHtml5ParserEnabled', true);
		$options->set('isRemoteEnabled', true);
		$options->set('tempDir', '/tmp');
		$options->set('defaultFont', 'DejaVu Sans');

		$dompdf = new Dompdf($options);
		$dompdf->loadHtml($html);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A6', 'portrait');
		$dompdf->set_option('defaultMediaType', 'all');
		$dompdf->set_option('isFontSubsettingEnabled', true);

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream('comanda.pdf', array('Attachment' => 0));
		exit();
	}

	public function pdfLista(Request $request, Response $response, array $args)
	{
		$params = $request->getQueryParams();
		$currentUser = $this->ci->currentUser;

		$categorie = Categoria::orderBy('ordine')->where('evento_id', $this->eventoCorrente()->id)->get();
		foreach ($categorie as $c) {
			$prodotti = Prodotto::where('categoria_id', $c->id)->orderBy('ordine')->get();
			$c->setAttribute('prodotti', $prodotti);
		}

		$html = $this->ci->view->fetch('pdf/lista.html.twig', [
			'categorie' => $categorie,
			'evento' => $this->eventoCorrente(),
			'comanda' => $comanda,
			'dettagli' => $dettagli,
			'postazioni_scontrino' => $postazioni_scontrino,
			'postazioni_no_scontrino' => $postazioni_no_scontrino,
			'utente' => $currentUser
		]);

		//$loader = new \Twig\Loader\FilesystemLoader('/srv/web/app/sprinkles/elezioni/templates');
		//$twig = new \Twig\Environment($loader, []);
		//$html = $response->getBody();
		//$html = "<h1>Ciao</h1>";

		$options = new Options();
		$options->set('isHtml5ParserEnabled', true);
		$options->set('isRemoteEnabled', true);
		$options->set('tempDir', '/tmp');
		$options->set('defaultFont', 'DejaVu Sans');

		$dompdf = new Dompdf($options);
		$dompdf->loadHtml($html);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->set_option('defaultMediaType', 'all');
		$dompdf->set_option('isFontSubsettingEnabled', true);

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream('comanda.pdf', array('Attachment' => 0));
		exit();
	}

	public function pdfRiepilogo(Request $request, Response $response, array $args)
	{
		$params = $request->getQueryParams();
		$currentUser = $this->ci->currentUser;

		$categorie = Categoria::where('evento_id', $this->eventoCorrente()->id)->get();
		foreach ($categorie as $c) {
			$prodotti = Prodotto::where('categoria_id', $c->id)->orderBy('ordine')->get();
			foreach ($prodotti as $p) {
				$conta = 0;
				$incasso = 0;
				$comande_dettagli = ComandaDettaglio::where('prodotto_id', $p->id)->get();
				foreach ($comande_dettagli as $cd) {
					$conta += $cd->quantita;
					$comanda = Comanda::find($cd->comanda_id);
					if ($comanda) {
						$perc = $comanda->pagato / $comanda->totale;
						$incasso += $p->prezzo * $cd->quantita * $perc;
					}
				}
				$p->setAttribute('conta', $conta);
				$p->setAttribute('incasso', $incasso);
			}
			$c->setAttribute('prodotti', $prodotti);
		}

		$html = $this->ci->view->fetch('pdf/riepilogo.html.twig', [
			'categorie' => $categorie,
			'evento' => $this->eventoCorrente(),
			'comanda' => $comanda,
			'dettagli' => $dettagli,
			'postazioni_scontrino' => $postazioni_scontrino,
			'postazioni_no_scontrino' => $postazioni_no_scontrino,
			'utente' => $currentUser
		]);

		//$loader = new \Twig\Loader\FilesystemLoader('/srv/web/app/sprinkles/elezioni/templates');
		//$twig = new \Twig\Environment($loader, []);
		//$html = $response->getBody();
		//$html = "<h1>Ciao</h1>";

		$options = new Options();
		$options->set('isHtml5ParserEnabled', true);
		$options->set('isRemoteEnabled', true);
		$options->set('tempDir', '/tmp');
		$options->set('defaultFont', 'DejaVu Sans');

		$dompdf = new Dompdf($options);
		$dompdf->loadHtml($html);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->set_option('defaultMediaType', 'all');
		$dompdf->set_option('isFontSubsettingEnabled', true);

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream('comanda.pdf', array('Attachment' => 0));
		exit();
	}

	public function pdfRiepilogoGenerale(Request $request, Response $response, array $args)
	{
		$params = $request->getQueryParams();
		$currentUser = $this->ci->currentUser;
		$nome_evento = "Festa di Santa Teresa 2023";
		$eventi_id = [5, 6, 7, 8];
		$eventi = Evento::whereIn('id', $eventi_id)->get();
		//$eventi = Evento::where('nome',$nome_evento)->get();
		$eventi_id = $eventi->pluck('id')->toArray();
		$arr_date = [];

		$perc_incassi = array();
		foreach ($eventi as $evento) {
			array_push($arr_date, Date('d/m', strtotime($evento->data)));
			$incasso = 0;
			$comande_pagate = Comanda::where('evento_id', $evento->id)->get();
			foreach ($comande_pagate as $c)
				$incasso += $c->pagato;
			$perc_incassi[$evento->id] = $evento->incasso_effettivo / $incasso;
		}
		$titolo_date = join(' - ', $arr_date);
		$cate_array = array();

		$categorie = Categoria::whereIn('evento_id', $eventi_id)->orderBy('ordine')->get();
		foreach ($categorie as $c) {
			if (!array_key_exists($c->nome, $cate_array)) {
				$cate_array[$c->nome] = Categoria::whereIn('evento_id', $eventi_id)->where('nome', $c->nome)->get()->pluck('id')->toArray();
			}
		}

		$cate_array2 = array();

		foreach ($cate_array as $categoria_nome => $categorie_id) {
			$prod_array = array();
			$prodotti = Prodotto::whereIn('categoria_id', $categorie_id)->get();
			foreach ($prodotti as $p) {
				if (!array_key_exists($p->nome, $prod_array)) {
					$prod_array[$p->nome] = Prodotto::whereIn('categoria_id', $categorie_id)->where('nome', $p->nome)->get()->pluck('id')->toArray();
				}
			}
			$cate_array2[$categoria_nome] = $prod_array;
		}
		$cate_array3 = array();
		foreach ($cate_array2 as $categoria_nome => $prod_array) {
			$cate_array3[$categoria_nome] = array();
			foreach ($prod_array as $nome_prodotto => $ids_prodotti) {
				$eventi2 = Evento::whereIn('id', $eventi_id)->get();
				foreach ($eventi2 as $e) {
					$conta = 0;
					$incasso = 0;
					$p = Prodotto::whereIn('id', $ids_prodotti)->first();
					$comande_dettagli = ComandaDettaglio::whereIn('prodotto_id', $ids_prodotti)->whereIn('comanda_id', Comanda::where('evento_id', $e->id)->get()->pluck('id')->toArray())->get();
					foreach ($comande_dettagli as $cd) {
						$conta += $cd->quantita;
						$comanda = Comanda::find($cd->comanda_id);
						if ($comanda) {
							$perc = $comanda->pagato / $comanda->totale;
							$incasso += $p->prezzo * $cd->quantita * $perc * $perc_incassi[$e->id];
						}
					}
					$e->setAttribute('conta', $conta);
					$e->setAttribute('incasso', $incasso);
				}
				$cate_array3[$categoria_nome][$nome_prodotto] = $eventi2;
			}
		}

		//$categorie = Categoria::get();

		$html = $this->ci->view->fetch('pdf/riepilogo-generale.html.twig', [
			'nome_evento' => $nome_evento,
			'titolo_date' => $titolo_date,
			'cate_array_3' => $cate_array3,
			'eventi' => $eventi,
			'comanda' => $comanda,
			'dettagli' => $dettagli,
			'postazioni_scontrino' => $postazioni_scontrino,
			'postazioni_no_scontrino' => $postazioni_no_scontrino,
			'utente' => $currentUser
		]);

		//$loader = new \Twig\Loader\FilesystemLoader('/srv/web/app/sprinkles/elezioni/templates');
		//$twig = new \Twig\Environment($loader, []);
		//$html = $response->getBody();
		//$html = "<h1>Ciao</h1>";

		$options = new Options();
		$options->set('isHtml5ParserEnabled', true);
		$options->set('isRemoteEnabled', true);
		$options->set('tempDir', '/tmp');
		$options->set('defaultFont', 'DejaVu Sans');

		$dompdf = new Dompdf($options);
		$dompdf->loadHtml($html);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A4', 'landscape');
		$dompdf->set_option('defaultMediaType', 'all');
		$dompdf->set_option('isFontSubsettingEnabled', true);

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream('comanda.pdf', array('Attachment' => 0));
		exit();
	}

	public function apiStampaComanda(Request $request, Response $response, array $args): Response
	{
		$params = $request->getQueryParams();
		$currentUser = $this->ci->currentUser;
		$comanda = Comanda::find($args["comanda_id"]);
		$cassiere = User::find($comanda->cassiere_id);
		if (!$comanda)
			throw new NotFoundException();
		$tipi = array("tutto", "scontrino-con-postazioni", "postazioni", "scontrino-senza-postazioni", "tutto_in_cassa_attiva");
		$tipo = $args["tipo"];
		if (!in_array($tipo, $tipi))
			throw new NotFoundException();
		$postazioni_id = array();
		$dettagli = ComandaDettaglio::where('comanda_id', $comanda->id)->get();
		foreach ($dettagli as $dettaglio) {
			$dettaglio->setAttribute('prodotto', Prodotto::find($dettaglio->prodotto_id));
			$postazione = Categoria::find($dettaglio->prodotto->categoria_id)->postazione_id;
			if (!in_array($postazione, $postazioni_id))
				array_push($postazioni_id, $postazione);
		}

		$postazioni_scontrino = Postazione::whereIn('id', $postazioni_id)->where('accoda_a_scontrino', true)->where('evento_id', $comanda->evento_id)->orderBy('ordine')->get();
		foreach ($postazioni_scontrino as $p) {
			$dettaglip = ComandaDettaglio::where('comanda_id', $comanda->id)->whereIn('prodotto_id', Prodotto::whereIn('categoria_id', Categoria::where('postazione_id', $p->id)->get()->pluck('id')->toArray())->get()->pluck('id')->toArray())->get();
			foreach ($dettaglip as $dettaglio) {
				$dettaglio->setAttribute('prodotto', Prodotto::find($dettaglio->prodotto_id));
			}
			$p->setAttribute('dettagli', $dettaglip);
		}
		$postazioni_no_scontrino = Postazione::whereIn('id', $postazioni_id)->where('accoda_a_scontrino', null)->where('evento_id', $comanda->evento_id)->orderBy('ordine')->get();
		foreach ($postazioni_no_scontrino as $p) {
			$dettaglip = ComandaDettaglio::where('comanda_id', $comanda->id)->whereIn('prodotto_id', Prodotto::whereIn('categoria_id', Categoria::where('postazione_id', $p->id)->get()->pluck('id')->toArray())->get()->pluck('id')->toArray())->get();
			foreach ($dettaglip as $dettaglio) {
				$dettaglio->setAttribute('prodotto', Prodotto::find($dettaglio->prodotto_id));
			}
			$p->setAttribute('dettagli', $dettaglip);
		}

		if ($tipo == "tutto" || $tipo == "scontrino-con-postazioni")
			$this->printScontrino($comanda, $dettagli, $postazioni_scontrino);
		elseif ($tipo == "scontrino-senza-postazioni")
			$this->printScontrino($comanda, $dettagli, []);
		if ($tipo == "tutto" || $tipo == "postazioni")
			$this->printPostazioni($comanda, $dettagli, $postazioni_no_scontrino);
		if ($tipo == "tutto_in_cassa_attiva") {
			$this->printScontrino($comanda, $dettagli, $postazioni_scontrino, true);
			$this->printPostazioni($comanda, $dettagli, $postazioni_no_scontrino, true);
		}


		return $this->ci->view->render($response, 'pages/comanda.html.twig', [
			'cassiere' => $cassiere,
			'evento' => $this->eventoCorrente(),
			'conti' => $conti,
			'comanda' => $comanda,
			'dettagli' => $dettagli,
			'categorie1' => $categorie1,
			'categorie2' => $categorie2,
			'utente' => $currentUser
		]);
	}

	private function printScontrinoOld()
	{

		// Enter connector and capability profile (to match your printer)
		$connector = new NetworkPrintConnector("10.19.4.42", 9100);
		$profile = CapabilityProfile::load("default");
		$verbose = false; // Skip tables which iconv wont convert to (ie, only print characters available with UTF-8 input)

		/* Print a series of receipts containing i18n example strings - Code below shouldn't need changing */
		$printer = new Printer($connector, $profile);
		$codePages = $profile->getCodePages();
		$first = true; // Print larger table for first code-page.
		foreach ($codePages as $table => $page) {
			/* Change printer code page */
			$printer->selectCharacterTable(255);
			$printer->selectCharacterTable($table);
			/* Select & print a label for it */
			$label = $page->getId();
			if (!$page->isEncodable()) {
				$label = " (not supported)";
			}
			$printer->setEmphasis(true);
			$printer->textRaw("Table $table: $label\n");
			$printer->setEmphasis(false);
			if (!$page->isEncodable() && !$verbose) {
				continue; // Skip non-recognised
			}
			/* Print a table of available characters (first table is larger than subsequent ones */
			if ($first) {
				$first = false;
				$this->compactCharTable($printer, 1, true);
			} else {
				$this->compactCharTable($printer);
			}
		}
		$printer->cut();
		$printer->close();
	}
	private function compactCharTable($printer, $start = 4, $header = false)
	{
		/* Output a compact character table for the current encoding */
		$chars = str_repeat(' ', 256);
		for ($i = 0; $i < 255; $i++) {
			$chars[$i] = ($i > 32 && $i != 127) ? chr($i) : ' ';
		}
		if ($header) {
			$printer->setEmphasis(true);
			$printer->textRaw("  0123456789ABCDEF0123456789ABCDEF\n");
			$printer->setEmphasis(false);
		}
		for ($y = $start; $y < 8; $y++) {
			$printer->setEmphasis(true);
			$printer->textRaw(strtoupper(dechex($y * 2)) . " ");
			$printer->setEmphasis(false);
			$printer->textRaw(substr($chars, $y * 32, 32) . "\n");
		}
	}


	private function printScontrino($comanda, $dettagli, $postazioni_scontrino, $stampa_cassa_attiva = false)
	{
		$evento = Evento::find($comanda->evento_id);
		$cassiere = User::find($comanda->cassiere_id);
		$cassa = Cassa::find($comanda->cassa_id);
		if (!$comanda)
			throw new NotFoundException();
		if (!$cassa)
			throw new NotFoundException();
		$stampante = Stampante::find($cassa->stampante_id);
		if ($stampa_cassa_attiva) {
			$cassa_corrente = $this->cassaCorrente();
			$stampante = Stampante::find($cassa_corrente->stampante_id);
		}
		$connector = new NetworkPrintConnector($stampante->ip, 9100);
		try {
			$printer = new Printer($connector);
			//$printer->setFont(Printer::FONT_B);
			$items = array();
			$subtotale = 0;
			foreach ($dettagli as $dettaglio) {
				$subtotale += $dettaglio->prodotto->prezzo * $dettaglio->quantita;
				$item = new item($dettaglio->quantita, $dettaglio->prodotto->nome, $dettaglio->prodotto->prezzo * $dettaglio->quantita);
				array_push($items, $item);
			}
			$subtotal = new item('', 'Subtotale', $subtotale);
			/* Date is kept the same for testing */
			$dataor_attuale = date('d/m/Y H:i:s');

			/* Nome dell'evento */
			$printer->setJustification(Printer::JUSTIFY_CENTER);
			$printer->setEmphasis(true);
			$printer->text($evento->nome . ' - ' . date("d/m/Y", strtotime($evento->data)));
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
			$euro = '$';
			$printer->setEmphasis(false);
			$printer->feed();
			$printer->setJustification(Printer::JUSTIFY_LEFT);
			foreach ($items as $item) {
				$printer->text($item->getAsString(48)); // for 58mm Font A
			}
			$printer->setEmphasis(true);
			$printer->text($subtotal->getAsString(48));
			$printer->setEmphasis(false);
			$printer->feed();

			$printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
			$printer->text("Totale          ");
			$printer->textRaw(chr($stampante->codice_euro));
			$printer->text(str_pad(number_format($subtotale, 2), 7, ' ', STR_PAD_LEFT));
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
		} catch (Exception $e) {
			echo $e->getMessage();
		} finally {
			$printer->close();
		}
	}

	private function printPostazioni($comanda, $dettagli, $postazioni_no_scontrino, $stampa_cassa_attiva = false)
	{
		$evento = Evento::find($comanda->evento_id);
		$cassiere = User::find($comanda->cassiere_id);
		$cassa = Cassa::find($comanda->cassa_id);
		if (!$comanda)
			throw new NotFoundException();
		if (!$cassa)
			throw new NotFoundException();
		//$stampante = Stampante::find($cassa->stampante_id);

		foreach ($postazioni_no_scontrino as $p) {
			if ($p->stampante_id)
				$stampante = Stampante::find($p->stampante_id);
			else
				$stampante = Stampante::find($cassa->stampante_id);
			if ($stampa_cassa_attiva) {
				$cassa_corrente = $this->cassaCorrente();
				$stampante = Stampante::find($cassa_corrente->stampante_id);
			}
			$connector = new NetworkPrintConnector($stampante->ip, 9100);
			try {
				$printer = new Printer($connector);
				$this->printPostazione($comanda, $p, $printer);
				$printer->cut();
			} catch (Exception $e) {
				echo $e->getMessage();
			} finally {
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
			$coperti = ComandaDettaglio::where('comanda_id', $comanda->id)->whereIn('prodotto_id', Prodotto::where('nome', "Coperto")->get()->pluck('id'))->first()->quantita;
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

	private function printOrdini($comanda)
	{
		$evento = Evento::find($comanda->evento_id);
		$cassiere = User::find($comanda->cassiere_id);
		if (!$comanda)
			throw new NotFoundException();

		$dettagli = ComandaDettaglio::where('comanda_id', $comanda->id)->get();
		foreach ($dettagli as $dettaglio) {
			$dettaglio->setAttribute('prodotto', Prodotto::find($dettaglio->prodotto_id));
		}

		$connector = new NetworkPrintConnector($evento->ip_stampante, 9100);
		try {
			$printer = new Printer($connector);
			$items = array();
			$subtotale = 0;
			foreach ($dettagli as $dettaglio) {
				$subtotale += $dettaglio->prodotto->prezzo * $dettaglio->quantita;
				$item = new item($dettaglio->quantita, $dettaglio->prodotto->nome, $dettaglio->prodotto->prezzo * $dettaglio->quantita);
				array_push($items, $item);
			}
			$subtotal = new item('', 'Subtotale', $subtotale);
			$total = new item('', 'Totale', $subtotale, true);
			/* Date is kept the same for testing */
			$dataor_attuale = date('d/m/Y H:i:s');

			/* Nome dell'evento */
			$printer->setJustification(Printer::JUSTIFY_CENTER);
			$printer->setEmphasis(true);
			$printer->text($evento->nome . ' - ' . date("d/m/Y", strtotime($evento->data)));
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
			$printer->text('$');
			$printer->setEmphasis(false);
			$printer->feed();
			$printer->setJustification(Printer::JUSTIFY_LEFT);
			foreach ($items as $item) {
				$printer->text($item->getAsString(48)); // for 58mm Font A
			}
			$printer->setEmphasis(true);
			$printer->text($subtotal->getAsString(48));
			$printer->setEmphasis(false);
			$printer->feed();

			$printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
			$printer->text($total->getAsString(48));
			$printer->selectPrintMode();
			$printer->feed(2);

			$printer->setJustification(Printer::JUSTIFY_CENTER);
			$printer->text(date('d/m/Y H:i:s'));
			/* Cut the receipt and open the cash drawer */
			$printer->feed();
			$printer->cut();
			$printer->pulse();
		} catch (Exception $e) {
			echo $e->getMessage();
		} finally {
			$printer->close();
		}
	}
	public function apriCassetto()
	{
		$cassa = $this->cassaCorrente();
		$stampante = Stampante::find($cassa->stampante_id);
		$connector = new NetworkPrintConnector($stampante->ip, 9100);
		try {
			$printer = new Printer($connector);
			$printer->pulse();
		} catch (Exception $e) {
			echo $e->getMessage();
		} finally {
			$printer->close();
		}
	}
}


class item
{
	private $quantita;
	private $name;
	private $price;
	private $dollarSign;

	public function __construct($quantita = '', $name = '', $price = '', $dollarSign = false)
	{
		$this->quantita = $quantita;
		$this->name = $name;
		$this->price = $price;
		$this->dollarSign = $dollarSign;
	}

	public function getAsString($width = 48)
	{
		$rightCols = 8;
		$leftCols = $width - $rightCols;
		if ($this->dollarSign) {
			$leftCols = $leftCols / 2 - $rightCols / 2;
		}
		$quantita = ($this->quantita ? $this->quantita . ' x ' : '');
		$left = str_pad($quantita . substr($this->name, 0, $leftCols - strlen($quantita)), $leftCols);
		$sign = ($this->dollarSign ? '$ ' : '');
		$right = str_pad($sign . number_format($this->price, 2), $rightCols, ' ', STR_PAD_LEFT);
		return "$left$right\n";
	}


	public function __toString()
	{
		return $this->getAsString();
	}
}
