<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspirasi', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('kata', function () {
    $this->comment(Inspiring::quote());
})->purpose('Menampilkan kata-kata');
