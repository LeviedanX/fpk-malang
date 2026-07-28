<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('admin:prune-activity-logs')
    ->dailyAt('02:30')
    ->withoutOverlapping();

Schedule::command('chat:prune')
    ->dailyAt('02:45')
    ->withoutOverlapping();
