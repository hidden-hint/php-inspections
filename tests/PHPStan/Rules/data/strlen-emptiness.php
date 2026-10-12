<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules\data;

final class StrlenEmptiness
{
    public function check(string $name, ?string $maybe, int $number): array
    {
        return [
            strlen($name) === 0,
            mb_strlen($name) > 0,
            strlen($maybe) === 0,
            strlen($number) === 0,
        ];
    }
}
