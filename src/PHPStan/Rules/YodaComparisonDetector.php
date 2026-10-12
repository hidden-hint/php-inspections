<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\PHPStan\Rules;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\InterpolatedString;

final class YodaComparisonDetector
{
    public function isNonYoda(Identical $comparison): bool
    {
        return $this->isConstantLike($comparison->right) && !$this->isConstantLike($comparison->left);
    }

    private function isConstantLike(Expr $expression): bool
    {
        return match (true) {
            $expression instanceof InterpolatedString => false,
            $expression instanceof Scalar, $expression instanceof ConstFetch, $expression instanceof ClassConstFetch => true,
            $expression instanceof Array_ => [] === $expression->items,
            $expression instanceof UnaryMinus => $expression->expr instanceof Scalar,
            default => false,
        };
    }
}
