<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('jasawarga:expire-bookings')->everyMinute()->withoutOverlapping();
