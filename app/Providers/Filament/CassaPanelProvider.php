<?php

namespace App\Providers\Filament;

use App\Filament\Resources\EventoResource\Widgets\EventoAttivoWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Vite;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Vite as FacadesVite;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class CassaPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('cassa')
            ->path('cassa')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Cassa/Resources'), for: 'App\\Filament\\Cassa\\Resources')
            ->discoverPages(in: app_path('Filament/Cassa/Pages'), for: 'App\\Filament\\Cassa\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Cassa/Widgets'), for: 'App\\Filament\\Cassa\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                EventoAttivoWidget::class
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->databaseTransactions()
            ->maxContentWidth(MaxWidth::Full)
            ->renderHook(
                'panels::head.start',
                fn(): string => FacadesVite::useHotFile('admin.hot')
                    ->useBuildDirectory('build')
                    ->withEntryPoints(['resources/css/app.css'])->toHtml()
            );
    }
}
