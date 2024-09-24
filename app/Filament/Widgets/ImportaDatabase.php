<?php

namespace App\Filament\Widgets;

use App\Models\Evento;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ImportaDatabase extends Widget implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.importa-database';

    /* protected function getViewData(): array
    {
        return [
            "evento_attivo" => Evento::where('attivo', 1)->first()
        ];
    }
*/
    public function prova($data)
    {
        dd("CIAO");
        //        $json = Storage::json($file);
        //      foreach ($json as $table)
        //        dd($table);
    }
    public function provaAction(): Action
    {
        return Action::make('prova')
            ->label("Importa JSON Database")
            ->form([
                FileUpload::make('json')
                    ->label("Importa JSON Database")
                    ->disk('local')
                    ->directory('json-db')
                    ->downloadable()
                    ->acceptedFileTypes(['application/json'])
                    ->required()
                    ->columns()
                    ->afterStateUpdated(function (callable $set, TemporaryUploadedFile $state) {
                        $set('fileRealPath', $state->getRealPath());
                    }),
                Hidden::make('fileRealPath'),
            ])
            ->action(function ($data) {
                $models = [
                    'casse' => 'App\Models\Cassa',
                    'categorie' => 'App\Models\Categoria',
                    'comande' => 'App\Models\Comanda',
                    'comande_dettagli' => 'App\Models\ComandaDettaglio',
                    'conti' => 'App\Models\Conto',
                    'eventi' => 'App\Models\Evento',
                    'postazioni' => 'App\Models\Postazione',
                    'prodotti' => 'App\Models\Prodotto',
                    'stampanti' => 'App\Models\Stampante',
                ];
                $json = Storage::json($data['json']);
                foreach ($json as $tabella => $rows) {

                    $model = $models[$tabella];
                    $model::truncate();
                    foreach ($rows as $row)
                        $model::create($row);
                }
            });
    }
}
