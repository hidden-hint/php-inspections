<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules\data;

final class ArrayMergeInLoop
{
    public function merge(array $chunks, array $defaults, array $groups): array
    {
        $result = [];
        foreach ($chunks as $chunk) {
            $result = array_merge($result, $chunk);
        }
        foreach ($chunks as $chunk) {
            $merged = array_merge($defaults, $chunk);
        }
        foreach ($chunks as $chunk) {
            $callback = static function (array $result) use ($chunk): array {
                $result = array_merge($result, $chunk);

                return $result;
            };
        }
        foreach ($groups as $group) {
            foreach ($group as $chunk) {
                $result = array_merge($result, $chunk);
            }
        }

        return $result;
    }
}

final class PropertyAccumulation
{
    private array $items = [];

    private static array $registry = [];

    public function collect(array $chunks, array $groups): void
    {
        foreach ($chunks as $chunk) {
            $this->items = array_merge($this->items, $chunk);
            self::$registry = array_merge(self::$registry, $chunk);
            $this->items = array_merge(self::$registry, $chunk);
        }
        foreach ($groups as $key => $group) {
            $groups[$key] = array_merge($groups[$key], $group);
        }
    }
}
