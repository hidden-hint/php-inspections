<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules\data;

use Countable;

final class CountEmptiness
{
    public function check(array $items, Countable $collection): array
    {
        return [
            count($items) === 0,
            0 < count($items),
            count($items) >= 1,
            count($collection) === 0,
            count($items) === 2,
        ];
    }

    /**
     * @param array<int> $documented
     */
    public function checkDocumented($documented): bool
    {
        return count($documented) === 0;
    }
}
