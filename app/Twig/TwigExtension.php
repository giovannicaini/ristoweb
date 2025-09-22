<?php
/*
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\Markup;
use Illuminate\Foundation\Vite;

class TwigExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('vite', [$this, 'vite']),
        ];
    }

    public function vite(string $resource): string
    {
        return new Markup((new Vite)->__invoke($resource), 'UTF-8');
    }
}
*/