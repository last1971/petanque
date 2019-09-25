<?php


namespace App\Services;


use Illuminate\Support\Collection;

class CollectionService
{
    public static function run()
    {
        if (!Collection::hasMacro('sortByMulti')) {
            /**
             * An extension of the {@see Collection::sortBy()} method that allows for sorting against as many different
             * keys. Uses a combination of {@see Collection::sortBy()} and {@see Collection::groupBy()} to achieve this.
             *
             * @param array $keys An associative array that uses the key to sort by (which accepts dot separated values,
             *                    as {@see Collection::sortBy()} would) and the value is the order (either ASC or DESC)
             */
            Collection::macro('sortByMulti', function (array $keys) {
                $keys = array_map(function ($key, $sort) {
                    return ['key' => $key, 'sort' => $sort];
                }, array_keys($keys), $keys);

                $sortBy = function (Collection $collection, $currentIndex) use ($keys, &$sortBy) {
                    if ($currentIndex >= count($keys)) {
                        return $collection;
                    }

                    $key = $keys[$currentIndex]['key'];
                    $sort = $keys[$currentIndex]['sort'];
                    $sortFunc = $sort === 'DESC' ? 'sortByDesc' : 'sortBy';

                    $sorted_collection = $collection->$sortFunc($key)->values();

                    $values = $sorted_collection->pluck($key)->unique()->values();

                    $ret = collect();

                    foreach ($values as $value) {
                        $current = $sorted_collection->filter(function($val) use ($key, $value) {
                            return $val[$key] == $value;
                        });
                        $ret = $ret->merge($sortBy($current, $currentIndex + 1));
                    }
                    return $ret;
                };
                $s= $sortBy($this, 0);
                return $sortBy($this, 0);
            });
        }

    }
}
