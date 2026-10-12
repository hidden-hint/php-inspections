<?php

namespace Example;

class Sample
{
    public function run(array $items): int
    {
        $filter = function (int $item) {
            return $item == 1;
        };
        try {
            $total = sizeof(array_filter($items, $filter));
        } catch (\Throwable $exception) {
            $total = 0;
        }
        if (in_array(2, $items)) {
            $total++;
        }
        return $total;
    }
}
