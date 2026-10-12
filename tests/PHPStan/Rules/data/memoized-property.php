<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules\data;

final class MemoizedProperty
{
    private ?array $memoizedItems = null;
    private $memoizedTotal;
    private array $memoizedValid;
    private ?array $items = null;
}
