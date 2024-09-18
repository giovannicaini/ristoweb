<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class ProdottoSlider extends Field
{
    protected string $view = 'forms.components.prodotto-slider';

    public function prova($state)
    {
        dd($state);
    }
}
