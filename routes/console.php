<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('database:backup')
    ->dailyAt('02:00')
    ->timezone('Africa/Accra')
    ->environments(['production'])
    ->when(fn (): bool => config('database.default') === 'sqlite')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/database-backups.log'));
