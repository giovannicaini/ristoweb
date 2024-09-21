<?php

use App\Http\Controllers\RistoController;
use App\Models\ComandaPostazione;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
	return view('welcome');
});


Route::get('/login', function () {
	return redirect(route('filament.admin.auth.login'));
})->name('login');

Route::get('/qr', function () {
	return view('qr');
})->name('qr');

Route::get('qr/{uuid}', function ($uuid) {
	$cp = ComandaPostazione::where('uuid',$uuid)->withoutGlobalScopes()->first();
	if ($cp && $cp->delivered)
		return "La comanda risulta già consegnata alle " . Date("H:i:s", strtotime($cp->delivered_at));
	else if ($cp){
		$cp->delivered_at = now();
		$cp->save();
		return "Comanda " . $cp->comanda->numero . " contrassegnata come consegnata";
	}
	else
		return "Comanda non trovata";
});


Route::prefix('/comande')->group(function () {
	Route::get('', [RistoController::class, 'pageComande']);
	Route::get('/prova', [RistoController::class, 'pageComande2']);
	Route::get('/lista', [RistoController::class, 'pdfLista']);
	Route::get('/riepilogo', [RistoController::class, 'pdfRiepilogo']);
	Route::get('/riepilogo-generale', [RistoController::class, 'pdfRiepilogoGenerale']);
	//Route::get('/errori', [RistoController::class, 'pageErroreGenitori');

})->middleware('auth');


Route::prefix('/comanda')->group(function () {
	Route::get('/{comanda_id}/pdf', [RistoController::class, 'pdfComanda']);
	Route::get('/{comanda_id}/stampa', [RistoController::class, 'pageComanda2']);
	Route::get('/{comanda_id}', [RistoController::class, 'pageComanda']);


	//Route::get('/errori', [RistoController::class, 'pageErroreGenitori');

})->middleware('auth');

Route::prefix('/modals/comande')->group(function () {
	Route::get('/nuova-comanda', [RistoController::class, 'modalNuovaComanda']);
	Route::get('/cambia-evento-corrente', [RistoController::class, 'modalCambiaEventoCorrente']);
	Route::get('/cambia-cassa-corrente', [RistoController::class, 'modalCambiaCassaCorrente']);
	Route::get('/invia-messaggio-cassa', [RistoController::class, 'modalInviaMessaggioCassa']);
	Route::get('/elimina-comanda/{comanda_id}', [RistoController::class, 'modalEliminaComanda']);
	//Route::get('/resetpwgenitore', [RistoController::class, 'modalResetPasswordGenitore');
})->middleware('auth');

Route::prefix('/api/comande')->group(function () {
	Route::post('/nuova-comanda', [RistoController::class, 'apiNuovaComanda']);
	Route::delete('/elimina-comanda/{comanda_id}', [RistoController::class, 'apiEliminaComanda']);
	Route::post('/cambia-evento-corrente', [RistoController::class, 'apiCambiaEventoCorrente']);
	Route::post('/cambia-cassa-corrente', [RistoController::class, 'apiCambiaCassaCorrente']);
	Route::post('/invia-messaggio-cassa', [RistoController::class, 'apiInviaMessaggioCassa']);
	Route::post('/compila-comanda/{comanda_id}', [RistoController::class, 'apiCompilaComanda']);
	Route::post('/invia-comanda/{comanda_id}', [RistoController::class, 'apiInviaComanda']);
	Route::post('/stampa/{comanda_id}', [RistoController::class, 'apiStampaComanda']);
	Route::post('/aggiorna/{campo}/{comanda_id}', [RistoController::class, 'apiAggiornaComanda']);
	Route::post('/apri-cassetto', [RistoController::class, 'apriCassetto']);

	//Route::get('/resetpwgenitore', [RistoController::class, 'modalResetPasswordGenitore');
})->middleware('auth');
Route::prefix('/api/comanda')->group(function () {
	Route::post('/{comanda_id}/stampa/{tipo}', [RistoController::class, 'apiStampaComanda']);
	//Route::get('/resetpwgenitore', [RistoController::class, 'modalResetPasswordGenitore');
})->middleware('auth');
