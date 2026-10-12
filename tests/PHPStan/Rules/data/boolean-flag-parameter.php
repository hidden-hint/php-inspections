<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules\data;

interface Toggle
{
    public function toggle(bool $enabled): void;
}

function run(bool $force): void {}

final class BooleanFlagParameter implements Toggle
{
    public function __construct(private bool $enabled) {}

    public function compare(?bool $strict): void {}

    public function toggle(bool $enabled): void {}

    public function identity(): callable
    {
        return static fn(bool $value): bool => $value;
    }
}
