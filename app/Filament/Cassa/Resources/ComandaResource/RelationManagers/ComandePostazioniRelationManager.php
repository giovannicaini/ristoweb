<?php

namespace App\Filament\Cassa\Resources\ComandaResource\RelationManagers;

use App\Actions\StampaScontrino;
use App\Actions\SyncComandePostazioni;
use Closure;
use Filament\Forms;
use Livewire\Component;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Contracts\View\View;
use Illuminate\View\View as ViewView;

class ComandePostazioniRelationManager extends RelationManager
{
    protected static string $relationship = 'comande_postazioni';

    protected static ?string $title = 'Stato Stampe Singole Postazioni';

    protected $listeners = ['refreshRelation' => '$refresh'];


    public function table(Table $table): Table
    {
        $ownerRecord = $this->ownerRecord;
        return $table
            ->recordTitleAttribute('postazione.nome')
            ->paginated(false)
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('postazione.nome')
                    ->sortable(),
                Tables\Columns\IconColumn::make('printed')
                    ->boolean()
                    ->label('Stampata'),
                Tables\Columns\TextColumn::make('printed_at')
                    ->sortable()
                    ->label('Orario di Stampa'),
                Tables\Columns\IconColumn::make('delivered')
                    ->boolean()
                    ->label('Consegnata'),
                Tables\Columns\TextColumn::make('delivered_at')
                    ->sortable()
                    ->label('Orario di Consegna'),
                Tables\Columns\TextColumn::make('attesa')
                    ->sortable()
                    ->label('Tempo di Attesa'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
            ])
            ->headerActions([
                Tables\Actions\Action::make()->make('syncPostazioni')
                    ->label('Sincronizza Postazioni [F1]')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function () use ($ownerRecord) {
                        SyncComandePostazioni::run($ownerRecord);
                    })
                    ->keyBindings(['f1'])
                    ->modalIcon('heroicon-o-arrow-path')
                    ->modalCancelAction(false),
                Tables\Actions\Action::make()->make('stampaAll')
                    ->label('Stampa Tutto [F2]')
                    ->modalHeading('Stampa Scontrino alla Cassa e Comande nelle varie postazioni')
                    ->icon('heroicon-o-printer')
                    ->requiresConfirmation()
                    ->action(function () use ($ownerRecord) {
                        StampaScontrino::run($ownerRecord, 'tutto');
                    })
                    ->keyBindings(['f2'])
                    ->modalIcon('heroicon-o-printer')
                    ->modalCancelAction(false)
            ])
            ->actions([
                Tables\Actions\Action::make('pippo')
                    ->action(function (Component $livewire) {
                        $livewire->dispatch('refreshComanda');
                    }),
                Tables\Actions\DeleteAction::make()
                    ->after(function (Component $livewire) {
                        $livewire->dispatch('refreshComanda');
                    }),
                Tables\Actions\ForceDeleteAction::make()
                    ->after(function (Component $livewire) {
                        $livewire->dispatch('refreshComanda');
                    }),
                Tables\Actions\RestoreAction::make()
                    ->after(function (Component $livewire) {
                        $livewire->dispatch('refreshComanda');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function (Component $livewire) {
                            $livewire->dispatch('refreshComanda');
                        }),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->after(function (Component $livewire) {
                            $livewire->dispatch('refreshComanda');
                        }),
                    Tables\Actions\RestoreBulkAction::make()
                        ->after(function (Component $livewire) {
                            $livewire->dispatch('refreshComanda');
                        }),
                ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]))
            ->contentFooter(function () use ($ownerRecord) {
                //return view('footer-pagamenti', ['comanda' => $ownerRecord]);
            });
    }

    protected function configureCreateAction(Tables\Actions\CreateAction $action): void
    {
        $action
            ->authorize(static fn(RelationManager $livewire): bool => (! $livewire->isReadOnly()) && $livewire->canCreate())
            ->form(fn(Form $form): Form => $this->form($form->columns(2)))
            ->modalDescription('Associa nuovo pagamento alla comanda');
    }
}
