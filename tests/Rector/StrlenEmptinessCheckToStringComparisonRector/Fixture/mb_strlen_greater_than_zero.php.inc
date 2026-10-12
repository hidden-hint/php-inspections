<?php

namespace HiddenHint\PhpInspections\Tests\Rector\StrlenEmptinessCheckToStringComparisonRector\Fixture;

final class MbStrlenGreaterThanZero
{
    public function run(string $name): bool
    {
        return mb_strlen($name) > 0;
    }
}

?>
-----
<?php

namespace HiddenHint\PhpInspections\Tests\Rector\StrlenEmptinessCheckToStringComparisonRector\Fixture;

final class MbStrlenGreaterThanZero
{
    public function run(string $name): bool
    {
        return '' !== $name;
    }
}

?>
