<?php


namespace App\Services;

use App\Team;
use Illuminate\Support\Collection;

class Pool
{
    /**
     * @var Collection
     */
    public $additional_teams;
    /**
     * @var Collection
     */
    public $teams;
    /**
     * @var int
     */
    protected $wins;
    /**
     * @var Collection
     */
    protected $sub1;
    /**
     * @var Collection
     */
    protected $sub2;
    /**
     * @var Collection
     */
    protected $sub2_keys;
    /**
     * @var int
     */
    protected $max_exch = 1;
    /**
     * @var int
     */
    protected $next_exch = 0;
    /**
     * @var int
     */
    protected $min_exch = 0;
    /**
     * @var int
     */
    protected $other_exch = 0;
    /**
     * @var bool
     */
    protected $flag_exch = true;
    /**
     * Pool constructor.
     * @param Collection $teams
     * @param int $wins
     */
    public function __construct(Collection $teams, int $wins)
    {
         $this->teams = $teams->values();
         $this->additional_teams = collect();
         $this->wins = $wins;
         $this->make_sub();
    }

    /**
     * @return bool|null
     */
    public function next_variant()
    {
        if ($this->shift_sub2()) {
            return $this->pre_pairing();
        }
        if ($this->exch_sub2()) {
            return $this->pre_pairing();
        }
        return null;
    }

    /**
     * @return bool|Collection
     */
    public function pairing()
    {
        $result = true;
        $this->make_sub(); //ФОрмируем подргуппы
        while (!$this->pre_pairing() && $result) { //пока не получаются пары
            if (!$this->shift_sub2()) { //если закончились варианты сдвига
                if (!$this->exch_sub2()) {//если закончились варианты одинарного обмена
                    $result = false;
                }
            }
        }
        // Возвращаем неудачу или команды без пары
        return $result ? $this->sub2_get()->slice($this->sub1->count())->values() : false;
    }

    public function not_sub()
    {
        return $this->sub1->count() == 0;
    }

    /**
     * @return int
     */
    public function get_wins()
    {
        return $this->wins;
    }

    /**
     * @return Collection
     */
    public function get_pairs() {
        $r = collect();
        $sub2 = $this->sub2_get();
        for ($i = 0; $i < $this->sub1->count(); $i++) {
            $r->push(collect([ $this->sub1[$i], $sub2->get($i) ]));
        }
        return $r;
    }

    /**
     * Однородная - true
     * @return bool
     */
    public function uniform()
    {
        return $this->additional_teams->count() == 0 || $this->additional_teams->count() > $this->teams->count();
    }

    /**
     * @return Collection
     */
    private function forming_new_additional()
    {
        return $this->sub2_get()->slice($this->sub1->count())->values();
    }

    /**
     * @param Collection $additional_teams
     */
    public function set_additional_teams(Collection $additional_teams)
    {
        $this->additional_teams = $additional_teams;
        $this->make_sub();
    }
    /**
     * Формируем подгруппы
     */
    private function  make_sub()
    {
        if ($this->uniform()) {
            $full = $this->additional_teams->merge($this->teams);
            $half = (int)($full->count() / 2);
            $this->sub1 = $full->filter(function ($value, $key) use ($half) {
                return $key < $half;
            })->values();
            $this->sub2 = $full->filter(function ($value, $key) use ($half) {
                return $key >= $half;
            })->values();
        } else {
            $this->sub1 = $this->additional_teams;
            $this->sub2 = $this->teams;
        }
        $this->sub2_keys = $this->sub2->keys();
    }

    /**
     * Проверяем что получатся все пары
     * @return bool
     */
    public function pre_pairing()
    {
        $possible = true;
        $sub2 = $this->sub2_get();
        for ($i = 0; $i < $this->sub1->count(); $i++)
        {
            if ($this->bad_pair($this->sub1[$i], $sub2->get($i))) {
                $possible = false;
                break;
            }
        }
        return $possible ? $this->forming_new_additional() : false;
    }

    /**
     * Нельзя создать пару - true
     * @param Team $team1
     * @param Team $team2
     * @return mixed
     */
    private function bad_pair(Team $team1, Team $team2)
    {
        return $team1->old_pairs->contains($team2->id);
    }

    /**
     * Перемещение
     * @return bool
     */
    public function shift_sub2()
    {
        if ($this->sub1->count() == 1) { // если в первой группе 1 элемент то циклический сдвиг
            $this->sub2_keys->push($this->sub2_keys->shift());
            if ($this->sub2_keys[0] == 0) { //цикл завершен
                return false; //вторая точка выхода
            }
        } else { // иначе алгорит Нарайаны
            $n = $this->sub2_keys->count();
            $l = $n - 1;
            $j = $l - 1;
            while ($this->sub2_keys[$j] > $this->sub2_keys[$j + 1]) {
                $j--;
                if ($j < 0) {
                    $this->sub2_keys = $this->sub2->keys();
                    return false; //вторая точка
                }
            }
            while ($this->sub2_keys[$l] < $this->sub2_keys[$j]) $l--;
            $team1 = $this->sub2_keys[$j];
            $team2 = $this->sub2_keys[$l];
            $this->sub2_keys->put($j, $team2);
            $this->sub2_keys->put($l, $team1);
            if ($j < $this->sub2_keys->count() - 2) {
                $i = 1;
                while ($j + $i < $n - $i) {
                    $team1 = $this->sub2_keys->get($j + $i);
                    $team2 = $this->sub2_keys->get($n - $i);
                    $this->sub2_keys->put($j + $i, $team2);
                    $this->sub2_keys->put($n - $i, $team1);
                    $i++;
                }
            }
        }
        return true; //первая точка выхода
    }

    /**
     * Обмен нужно доделать сортировку
     * @return bool
     */
    public function exch_sub2() {
        if ($this->next_exch == $this->sub1->count() || $this->other_exch == $this->sub2->count()) {
            return false;// вторая точка выхода
        }
        //
        $e = $this->sub1[$this->next_exch];
        $this->sub1->put($this->next_exch, $this->sub2[$this->other_exch]);
        $this->sub2->put($this->other_exch, $e);
        //
        if ($this->next_exch == $this->min_exch) {
            $this->next_exch = $this->max_exch;
            if ($this->max_exch < $this->sub1->count() - 1) {
                $this->max_exch++;
            } else {
                if ($this->flag_exch) {
                    $this->flag_exch = false;
                } else {
                    $this->max_exch++;
                }
            }
            $this->other_exch = $this->min_exch;
        } else {
            $this->next_exch--;
            $this->other_exch++;
        }
        return true;
    }

    /**
     * @return Collection
     */
    public function sub2_get()
    {
        return $this->sub2_keys->map(function($value) {
            return $this->sub2->get($value);
        });
    }
}
