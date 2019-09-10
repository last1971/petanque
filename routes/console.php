<?php

use App\Services\GroupService;
use App\Services\RoundService;
use App\Services\TeamService;
use App\Track;
use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

Artisan::command('test', function () {
   //$s = new RoundService();
   //$s->create(1);
   // $s = new TeamService();
   // dd($s->renumber(14));
    echo ceil(4 / 2);
})->describe('Test');
