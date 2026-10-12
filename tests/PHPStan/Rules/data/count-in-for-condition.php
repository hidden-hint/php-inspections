<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules\data;

final class CountInForCondition
{
    public function iterate(array $items, string $text): void
    {
        for ($index = 0; $index < count($items); $index++) {
        }
        for ($index = 0, $total = count($items); $index < $total; $index++) {
        }
        for ($index = 0; $index < mb_strlen($text); $index++) {
        }
    }
}
