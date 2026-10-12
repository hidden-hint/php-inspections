<?php

namespace HiddenHint\PhpInspections\Tests\Rector\CountEmptinessCheckToArrayComparisonRector\Fixture;

final class ZeroLessThanCount
{
    public function run(array $items): bool
    {
        return 0 < count($items);
    }
}

?>
-----
<?php

namespace HiddenHint\PhpInspections\Tests\Rector\CountEmptinessCheckToArrayComparisonRector\Fixture;

final class ZeroLessThanCount
{
    public function run(array $items): bool
    {
        return [] !== $items;
    }
}

?>
