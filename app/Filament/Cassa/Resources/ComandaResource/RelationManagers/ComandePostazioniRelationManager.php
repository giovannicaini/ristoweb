<?php

namespace App\Filament\Cassa\Resources\ComandaResource\RelationManagers;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\CreateAction;
use Filament\Schemas\Schema;
use App\Actions\StampaScontrino;
use App\Actions\SyncComandePostazioni;
use Closure;
use Filament\Forms;
use Livewire\Component;
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
                TextColumn::make('postazione.nome')
                    ->sortable(),
                IconColumn::make('printed')
                    ->boolean()
                    ->label('Stampata'),
                TextColumn::make('printed_at')
                    ->sortable()
                    ->label('Orario di Stampa'),
                IconColumn::make('delivered')
                    ->boolean()
                    ->label('Consegnata'),
                TextColumn::make('delivered_at')
                    ->sortable()
                    ->label('Orario di Consegna'),
                TextColumn::make('attesa')
                    ->sortable()
                    ->label('Tempo di Attesa'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
            ])
            ->headerActions([
                Action::make()->make('syncPostazioni')
                    ->label('Sincronizza Postazioni [F1]')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function () use ($ownerRecord) {
                        SyncComandePostazioni::run($ownerRecord);
                    })
                    ->keyBindings(['f1'])
                    ->modalIcon('heroicon-o-arrow-path')
                    ->modalCancelAction(false),
                Action::make()->make('stampaAll')
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
            ->recordActions([
                Action::make('pippo')
                    ->action(function (Component $livewire) {
                        $livewire->dispatch('refreshComanda');
                    }),
                DeleteAction::make()
                    ->after(function (Component $livewire) {
                        $livewire->dispatch('refreshComanda');
                    }),
                ForceDeleteAction::make()
                    ->after(function (Component $livewire) {
                        $livewire->dispatch('refreshComanda');
                    }),
                RestoreAction::make()
                    ->after(function (Component $livewire) {
                        $livewire->dispatch('refreshComanda');
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(function (Component $livewire) {
                            $livewire->dispatch('refreshComanda');
                        }),
                    ForceDeleteBulkAction::make()
                        ->after(function (Component $livewire) {
                            $livewire->dispatch('refreshComanda');
                        }),
                    RestoreBulkAction::make()
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

    protected function configureCreateAction(CreateAction $action): void
    {
        $action
            ->authorize(static fn(RelationManager $livewire): bool => (! $livewire->isReadOnly()) && $livewire->canCreate())
            ->schema(fn(Schema $schema): Schema => $this->form($schema->columns(2)))
            ->modalDescription('Associa nuovo pagamento alla comanda');
    }
}
