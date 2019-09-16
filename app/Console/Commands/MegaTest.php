<?php

namespace App\Console\Commands;

use App\Services\EventService;
use App\Services\GroupService;
use App\Services\RoundService;
use App\Services\TeamService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MegaTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        for ($j = 0; $j < 100; $j++) {
            $event = (new EventService())->store([
                'name' => 'TEST' . uniqid(),
                'date' => Carbon::now()->format('d/m/Y'),
                'rounds' => 5,
                'user_id' => 1
            ]);
            $event->tracks()->sync([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
            $group = (new GroupService())->store($event->id);
            for ($i = 1; $i < 21; $i++) {
                $team = (new TeamService())->store((string)$i);
                $group->teams()->attach($team->id);
            }
            for ($i = 0; $i < 5; $i++) {
                $round = (new RoundService())->create_v3($group->id);
                foreach ($round->games as $game) {
                    $game->load('members');
                    $points1 = random_int(0, 13);
                    $points2 = random_int(0, 13);
                    while ($points2 == $points1) {
                        $points2 = random_int(0, 13);
                    }
                    if ($points1 > $points2) {
                        $game->members[0]->update([
                            'points' => $points1,
                            'winner' => true,
                            'diff' => $points1 - $points2
                        ]);
                        $game->members[1]->update([
                            'points' => $points2,
                            'winner' => false,
                            'diff' => $points2 - $points1
                        ]);
                    } else {
                        $game->members[0]->update([
                            'points' => $points1,
                            'winner' => false,
                            'diff' => $points1 - $points2
                        ]);
                        $game->members[1]->update([
                            'points' => $points2,
                            'winner' => true,
                            'diff' => $points2 - $points1
                        ]);
                    }
                }
            }
        }
    }
}
