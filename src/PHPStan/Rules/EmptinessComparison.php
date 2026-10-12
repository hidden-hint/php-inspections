<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\PHPStan\Rules;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;

final readonly class EmptinessComparison
{
    public function __construct(
        public FuncCall $call,
        public Expr $subject,
        public bool $checksEmpty,
    ) {}
}
