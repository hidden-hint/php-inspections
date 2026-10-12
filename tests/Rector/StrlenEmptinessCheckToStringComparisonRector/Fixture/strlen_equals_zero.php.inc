<?php

namespace HiddenHint\PhpInspections\Tests\Rector\StrlenEmptinessCheckToStringComparisonRector\Fixture;

final class StrlenEqualsZero
{
    public function run(string $name): bool
    {
        return strlen($name) === 0;
    }
}

?>
-----
<?php

namespace HiddenHint\PhpInspections\Tests\Rector\StrlenEmptinessCheckToStringComparisonRector\Fixture;

final class StrlenEqualsZero
{
    public function run(string $name): bool
    {
        return '' === $name;
    }
}

?>
