<?php

namespace App\Filament\Resources\ProdottoResource\Widgets;

use App\Models\ComandaDettaglio;
use App\Models\Prodotto;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ProdottoChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'prodottoChart';
    protected int | string | array $columnSpan = 2;

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Report Prodotti Venduti';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $prodotti = Prodotto::get()->sortBy('ordine')->sortBy('categoria.ordine');
        $quantita = [];
        foreach ($prodotti as $prodotto) {
            $quantita[] = $prodotto->comande_dettagli->sum("quantita");
        }
        return [
            'chart' => [
                'type' => 'bar',
                'height' => 800,
            ],
            'series' => [
                [
                    'name' => 'Quantità',
                    'data' => $quantita,
                ],
            ],
            'xaxis' => [
                'categories' => $prodotti->pluck('nome'),
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#f59e0b'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 3,
                    'horizontal' => true,
                ],
            ],
        ];
    }
}
