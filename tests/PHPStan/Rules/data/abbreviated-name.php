<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules\data;

use Throwable;

final class AbbreviatedName
{
    private string $buf = '';

    public function handle(array $items, string $cfg): void
    {
        try {
            $id = 1;
            $url = 'https://example.com';
            $sku = 'ABC-1';
        } catch (Throwable $e) {
        }
        foreach ($items as $k => $v) {
        }
        $tmp = 1;
        $tmp = 2;
        $exception = null;
    }
}
