<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('operations:heartbeat')->everyMinute()->withoutOverlapping();
Schedule::command('content:publish-scheduled --batch=100')->everyMinute()->withoutOverlapping();
Schedule::command('commerce:expire-reservations --batch=100')->everyMinute()->withoutOverlapping();
Schedule::command('commerce:expire-orders --batch=100')->everyMinute()->withoutOverlapping();
Schedule::command('operations:monitor')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('operations:reconcile-payments --batch=25')->everyFifteenMinutes()->withoutOverlapping(30);
Schedule::command('operations:prune --batch=100')->dailyAt('02:30')->withoutOverlapping();
