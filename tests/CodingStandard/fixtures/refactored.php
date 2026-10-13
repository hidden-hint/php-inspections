<?php

declare(strict_types=1);

namespace Example;

use Throwable;

final class Sample
{
    public function run(array $items): int
    {
        if ([] === $items) {
            return 0;
        }
        $filter = static function (int $item) {
            return $item === 1;
        };
        try {
            $total = count(array_filter($items, $filter));
        } catch (Throwable $exception) {
            $total = 0;
        }
        if (in_array(2, $items, true)) {
            $total++;
        }

        return $total;
    }
}
