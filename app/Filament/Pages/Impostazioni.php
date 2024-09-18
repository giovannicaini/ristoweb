<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ImportaDatabase;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\StatsOverviewWidget;

class Impostazioni extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.impostazioni';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->url(''),
            Action::make('delete')
                ->requiresConfirmation()
                ->action(fn() => $this->post->delete()),
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }


    protected function getHeaderWidgets(): array
    {
        return [
            AccountWidget::class,
            ImportaDatabase::class
        ];
    }
}
