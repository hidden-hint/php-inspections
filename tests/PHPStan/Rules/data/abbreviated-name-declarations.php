<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules\data;

final class AbbreviatedNameDeclarations
{
    public function handle(array $pairs, array $rows, bool $flag): void
    {
        [$k, $v] = $pairs;
        list($cfg) = $pairs;
        foreach ($rows as [$idx, 'value' => $val]) {
        }
        static $tmp = 0;
        if ($flag) {
            $buf = 1;
        } else {
            $buf = 2;
        }
        foreach ($rows as $res) {
        }
        foreach ($rows as $res) {
        }
        $callback = static function (): int {
            $buf = 3;

            return $buf;
        };
    }
}
