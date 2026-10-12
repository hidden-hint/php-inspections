<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\data;

final class ExistingPacks
{
    private array $memoizedItems;

    public function items(): array
    {
        if (!isset($this->memoizedItems)) {
            $this->memoizedItems = [1];
        }

        return $this->memoizedItems;
    }

    public function isBlank(string $value): bool
    {
        return empty($value);
    }

    public function isOne(int $value): bool
    {
        return $value == 1;
    }
}
