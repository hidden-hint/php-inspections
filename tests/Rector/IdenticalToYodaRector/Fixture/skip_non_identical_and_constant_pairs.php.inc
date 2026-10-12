<?php

namespace HiddenHint\PhpInspections\Tests\Rector\IdenticalToYodaRector\Fixture;

final class SkipNonIdenticalAndConstantPairs
{
    public function run(mixed $value, mixed $other): array
    {
        return [$value !== null, null === $value, 1 === 2, $value === $other, $value === "x{$other}"];
    }
}

?>
