<?php

namespace HiddenHint\PhpInspections\Tests\Rector\StrlenEmptinessCheckToStringComparisonRector\Fixture;

final class SkipPhpdocOnlyString
{
    /**
     * @param string $name
     */
    public function run($name): bool
    {
        return strlen($name) > 0;
    }
}

?>
