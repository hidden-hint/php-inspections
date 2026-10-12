<?php

declare(strict_types=1);

final class Sample
{
    public function isBlank(string $name): bool
    {
        return '' === $name;
    }
}
