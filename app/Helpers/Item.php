<?php

namespace App\Helpers;

class Item
{
    private $quantita;
    private $name;
    private $price;
    private $dollarSign;

    public function __construct($quantita = '', $name = '', $price = '', $dollarSign = false)
    {
        $this->quantita = $quantita;
        $this->name = $name;
        $this->price = $price;
        $this->dollarSign = $dollarSign;
    }

    public function getAsString($width = 48)
    {
        $rightCols = 8;
        $leftCols = $width - $rightCols;
        if ($this->dollarSign) {
            $leftCols = $leftCols / 2 - $rightCols / 2;
        }
        $quantita = ($this->quantita ? $this->quantita . ' x ' : '');
        $left = str_pad($quantita . substr($this->name, 0, $leftCols - strlen($quantita)), $leftCols);
        $sign = ($this->dollarSign ? '$ ' : '');
        $right = str_pad($sign . number_format($this->price, 2), $rightCols, ' ', STR_PAD_LEFT);
        return "$left$right\n";
    }


    public function __toString()
    {
        return $this->getAsString();
    }
}
