<?php

namespace HiddenHint\PhpInspections\Tests\Rector\CountEmptinessCheckToArrayComparisonRector\Fixture;

use Countable;

final class SkipCountable
{
    public function run(Countable $collection): bool
    {
        return count($collection) === 0;
    }
}

?>
