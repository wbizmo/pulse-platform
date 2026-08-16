<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('content:publish-scheduled')->everyMinute()->withoutOverlapping();
Schedule::command('commerce:expire-reservations --batch=100')->everyMinute()->withoutOverlapping();
Schedule::command('commerce:expire-orders --batch=100')->everyMinute()->withoutOverlapping();
