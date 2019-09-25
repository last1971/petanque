<?php

namespace App\Http\Controllers;

use App\Services\TeamService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ResultController extends Controller
{
    //
    public function show($guid)
    {
        $id1 = Cache::pull($guid);
        if (!$id1) abort(404);
        $service = new TeamService();
        $teams = $service->rating($id1)->get();
        foreach ($teams as $team) {
            $team->was_names = $service->was($team->id, $id1);
            $team->mega_buhgolc = $teams->filter(function($value) use ($team) {
                return $team->was_names->contains($value->name);
            })->reduce(function($mega_buhgolc, $value) {
                return $mega_buhgolc + $value->buhgolc;
            });
        }

        return view(
            'result',
            [
                'teams' => $teams->sortByMulti([
                    'winner' => 'DESC',
                    'buhgolc' => 'DESC',
                    'mega_buhgolc' => 'DESC',
                    'points' => 'DESC'
                ])->values()
            ]
        );
    }
}
