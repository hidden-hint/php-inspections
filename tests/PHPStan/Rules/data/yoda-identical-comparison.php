<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules\data;

final class YodaIdenticalComparison
{
    private const string FOO = 'foo';

    public function compare(mixed $x, mixed $a, mixed $b): array
    {
        return [
            $x === null,
            null === $x,
            $x === self::FOO,
            $x === [],
            $x !== null,
            1 === 2,
            $a === $b,
            $x === -1,
        ];
    }
}
