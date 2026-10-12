<?php

namespace HiddenHint\PhpInspections\Tests\Rector\StrlenEmptinessCheckToStringComparisonRector\Fixture;

final class SkipNullableString
{
    public function run(?string $name): bool
    {
        return strlen($name) === 0;
    }
}

?>
