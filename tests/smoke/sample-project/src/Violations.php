<?php

declare(strict_types=1);

namespace Smoke;

final class Violations
{
    private const LIMIT = 10;

    public function publish(bool $force): void {}

    public function describe(string $str): string
    {
        return $str;
    }

    public function isEmpty(array $items): bool
    {
        return count($items) === 0;
    }

    public function flatten(array $chunks): array
    {
        $result = [];
        foreach ($chunks as $chunk) {
            $result = array_merge($result, $chunk);
        }

        return $result;
    }
}
