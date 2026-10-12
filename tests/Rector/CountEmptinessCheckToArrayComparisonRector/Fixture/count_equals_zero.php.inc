<?php

namespace HiddenHint\PhpInspections\Tests\Rector\CountEmptinessCheckToArrayComparisonRector\Fixture;

final class CountEqualsZero
{
    public function run(array $items): bool
    {
        return count($items) === 0;
    }
}

?>
-----
<?php

namespace HiddenHint\PhpInspections\Tests\Rector\CountEmptinessCheckToArrayComparisonRector\Fixture;

final class CountEqualsZero
{
    public function run(array $items): bool
    {
        return [] === $items;
    }
}

?>
