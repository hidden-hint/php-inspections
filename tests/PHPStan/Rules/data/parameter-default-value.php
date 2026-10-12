<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules\data;

abstract class Repository
{
    abstract public function fetch(int $limit): array;
}

final class ParameterDefaultValue extends Repository
{
    public function __construct(private int $limit = 10) {}

    public function page(int $limit = 10): void {}

    public function fetch(int $limit = 20): array
    {
        return [];
    }

    public function filter(): callable
    {
        return static function (int $limit = 5): int {
            return $limit;
        };
    }
}
