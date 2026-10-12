<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\InterpolatedString;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Identical>
 */
final class YodaIdenticalComparisonRule implements Rule
{
    private const string MESSAGE = 'Use Yoda notation: put the constant operand on the left side of ===.';
    private const string IDENTIFIER = 'phpInspections.yodaIdentical';

    public function getNodeType(): string
    {
        return Identical::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->isConstantLike($node->right) || $this->isConstantLike($node->left)) {
            return [];
        }

        return [RuleErrorBuilder::message(self::MESSAGE)->identifier(self::IDENTIFIER)->build()];
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
