<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use BezhanSalleh\PanelSwitch\PanelSwitch;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

        /*Livewire::setUpdateRoute(function ($handle) {
            return Route::post(config('subfolder') . 'livewire/update', $handle);
        });*/
        PanelSwitch::configureUsing(function (PanelSwitch $panelSwitch) {
            $panelSwitch
                ->modalHeading('Pannelli Disponibili')
                ->modalWidth('sm')
                ->slideOver()
                ->icons([
                    'admin' => 'heroicon-o-user-plus',
                    'cassa' => 'heroicon-o-banknotes',
                ])
                ->iconSize(16)
                ->labels([
                    'admin' => 'Admin',
                    'cassa' => 'Cassa'
                ]);
        });
    }
}
