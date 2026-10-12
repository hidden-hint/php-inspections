<?php

declare(strict_types=1);

final class Sample
{
    public function isBlank(string $name): bool
    {
        return '' === $name;
    }

    public function hasNoItems(array $items): bool
    {
        return [] === $items;
    }

    public function isMissing(?string $name): bool
    {
        return null === $name;
    }
}
