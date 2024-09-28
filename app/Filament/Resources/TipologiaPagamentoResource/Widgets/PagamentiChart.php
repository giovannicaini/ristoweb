<?php

namespace App\Filament\Resources\TipologiaPagamentoResource\Widgets;

use App\Models\TipologiaPagamento;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class PagamentiChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'pagamentiChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Report Tipologia Pagamenti';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $tipo_pagamenti = TipologiaPagamento::get();
        $quantita = [];
        foreach ($tipo_pagamenti as $tipo_pagamento) {
            $quantita[] = $tipo_pagamento->pagamenti->sum("importo");
        }
        return [
            'chart' => [
                'type' => 'pie',
                'height' => 300,
            ],
            'series' => $quantita,
            'labels' => $tipo_pagamenti->pluck('nome'),
            'legend' => [
                'labels' => [
                    'fontFamily' => 'inherit',
                ],
            ],
        ];
    }
}
