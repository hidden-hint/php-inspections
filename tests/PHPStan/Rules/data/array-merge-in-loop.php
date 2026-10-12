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
