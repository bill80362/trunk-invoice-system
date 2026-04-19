<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = '儀表板';

    public function getColumns(): int|array
    {
        return 3;
    }
}
