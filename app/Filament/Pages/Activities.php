<?php

namespace App\Filament\Pages;

use Noxo\FilamentActivityLog\Pages\ListActivities;

class Activities extends ListActivities
{
    // protected bool $isCollapsible = true;

    // protected bool $isCollapsed = false;

    public function getTitle(): string
    {
        return "Log Attività";
    }

    public static function getNavigationLabel(): string
    {
        return "Log Attività";
    }
}
