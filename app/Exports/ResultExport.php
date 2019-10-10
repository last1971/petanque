<?php

namespace App\Exports;

use App\Services\TeamService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;

class ResultExport implements FromCollection
{
    use Exportable;

    /**
     * @var int
     */
    protected $id;

    /**
     * ResultExport constructor.
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        //
        $lines = collect();
        $lines->push(collect(['№', 'Наименование', 'Побед', 'Бухгольц', 'МегаБухгольц', 'Разница']));
        $service = new TeamService();
        $teams = $service->rating($this->id)->get();
        foreach ($teams as $team) {
            $team->was_names = $service->was($team->id, $this->id);
            $team->mega_buhgolc = $teams->filter(function($value) use ($team) {
                return $team->was_names->contains($value->name);
            })->reduce(function($mega_buhgolc, $value) {
                return $mega_buhgolc + $value->buhgolc;
            });
        }
        $i = 1;
        foreach ($teams->sortByMulti([
            'winner' => 'DESC',
            'buhgolc' => 'DESC',
            'mega_buhgolc' => 'DESC',
            'points' => 'DESC'
        ])->values() as $team) {
            $lines->push(collect([
                $i++, $team->name, $team->winner, $team->buhgolc, $team->mega_buhgolc, $team->points
            ]));
        }
        return $lines;
    }
}
