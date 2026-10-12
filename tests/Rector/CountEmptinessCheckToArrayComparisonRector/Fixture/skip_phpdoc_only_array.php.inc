<?php

namespace HiddenHint\PhpInspections\Tests\Rector\CountEmptinessCheckToArrayComparisonRector\Fixture;

final class SkipPhpdocOnlyArray
{
    /**
     * @param array<int> $items
     */
    public function run($items): bool
    {
        return count($items) === 0;
    }
}

?>
