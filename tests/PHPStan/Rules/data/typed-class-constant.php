<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules\data;

final class TypedClassConstant
{
    private const int TYPED = 1;
    private const LIMIT = 10;
}

enum Status
{
    public const string LABEL = 'status';
    public const DEFAULT = 'draft';
}
