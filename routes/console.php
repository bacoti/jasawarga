<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment('JasaWarga — Gotong royong digital.');
})->purpose('Display a short message');

// Tahap 4: scheduler kedaluwarsa booking PostgreSQL.
require __DIR__.'/console-stage4.php';
