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
    //$a = collect([['id' => 4, 'name' => 22], ['id' => 1, 'name' => 2],['id' => 2, 'name' => 3], ['id' => 3, 'name' => 22] ]);
    //$a->push($a->shift());
    //dd($a);

 $s = new RoundService();
  dd($s->create_v3(21));
   // $s = new TeamService();
   // dd($s->renumber(14));
$a=1;
})->describe('Test');

Artisan::command('e_test', function () {
    $s1 = [ 1 ];
    $s2 = [ 5 ];
    $max_e = 1;
    $next_e = 0;
    $min_e = 0;
    $other_e = 0;
    $flag = true;
    while (true) {
        //
        $e = $s1[$next_e];
        $s1[$next_e] = $s2[$other_e];
        $s2[$other_e] = $e;
        //
        if ($next_e == $min_e) {
            $next_e = $max_e;
            if ($max_e < count($s1) -1) {
                $max_e++;
            } else {
                if ($flag) {
                    $flag = false;
                } else {
                    $min_e++;
                }
            }
            $other_e = $min_e;
        } else {
            $next_e--;
            $other_e++;
        }
    }
 })->describe('Display an inspiring quote');
