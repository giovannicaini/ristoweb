<?php

namespace App\Filament\Pages;

use App\Filament\Cassa\Widgets\SelectCassaWidget;
use App\Filament\Resources\EventoResource\Widgets\EventoAttivoWidget;
use App\Filament\Resources\ProdottoResource\Widgets\ProdottoChart;
use App\Filament\Resources\TipologiaPagamentoResource\Widgets\PagamentiChart;
use App\Filament\Widgets\ImportaDatabase;
use App\Models\Cassa;
use App\Models\Evento;
use Filament\Actions\Action as ActionsAction;
use Filament\Actions\Modal\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Support\Facades\FilamentIcon;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Contracts\Support\Htmlable;
use Phpsa\FilamentAuthentication\Widgets\LatestUsersWidget;

class Dashboard extends Page
{
    protected static string $routePath = '/';

    protected static ?int $navigationSort = -2;

    /**
     * @var view-string
     */
    protected static string $view = 'filament-panels::pages.dashboard';

    public static function getNavigationLabel(): string
    {
        return static::$navigationLabel ??
            static::$title ??
            __('filament-panels::pages/dashboard.title');
    }

    public static function getNavigationIcon(): string | Htmlable | null
    {
        return static::$navigationIcon
            ?? FilamentIcon::resolve('panels::pages.dashboard.navigation-item')
            ?? (Filament::hasTopNavigation() ? 'heroicon-m-home' : 'heroicon-o-home');
    }

    public static function getRoutePath(): string
    {
        return static::$routePath;
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return [
            AccountWidget::class,
            SelectCassaWidget::class,
            LatestUsersWidget::class,
            PagamentiChart::class,
            ProdottoChart::class,
        ];
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getVisibleWidgets(): array
    {
        return $this->filterVisibleWidgets($this->getWidgets());
    }

    /**
     * @return int | string | array<string, int | string | null>
     */
    public function getColumns(): int | string | array
    {
        return 2;
    }

    public function getTitle(): string | Htmlable
    {
        return static::$title ?? __('filament-panels::pages/dashboard.title');
    }

    public $defaultAction = 'onboarding';

    public function onboardingAction(): ActionsAction
    {
        return ActionsAction::make('onboarding')
            ->modalHeading('Imposta Cassa e Evento')
            ->form([
                Select::make('evento_id')
                    ->label("EVENTO")
                    ->options(Evento::orderBy('id', 'DESC')->get()->pluck('descrizione', 'id'))
                    ->columnSpan(1)
                    ->required()
                    ->live(),
                Select::make('cassa_id')
                    ->label("CASSA")
                    ->options(Cassa::orderBy('id', 'ASC')->pluck('nome', 'id'))
                    ->columnSpan(1)
                    ->required()
                    ->live()
            ])
            ->visible(fn(): bool => !Cassa::Corrente() | !Evento::Corrente())
            ->action(function ($data) {
                //dd($data);
                session(['cassa_corrente_id' => $data["cassa_id"]]);
                session(['evento_corrente_id' => $data["evento_id"]]);
            });
    }
}
