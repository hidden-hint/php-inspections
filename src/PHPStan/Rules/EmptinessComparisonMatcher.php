<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\PHPStan\Rules;

use LogicException;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Int_;

use function array_key_exists;
use function array_merge;
use function in_array;

final class EmptinessComparisonMatcher
{
    private const string NO_MATCH_MESSAGE = 'The comparison is not an emptiness check; call matches() first.';

    private const array CALL_ON_LEFT_FORMS = [
        Identical::class => ['literal' => 0, 'checksEmpty' => true],
        Equal::class => ['literal' => 0, 'checksEmpty' => true],
        NotIdentical::class => ['literal' => 0, 'checksEmpty' => false],
        NotEqual::class => ['literal' => 0, 'checksEmpty' => false],
        Greater::class => ['literal' => 0, 'checksEmpty' => false],
        Smaller::class => ['literal' => 1, 'checksEmpty' => true],
        GreaterOrEqual::class => ['literal' => 1, 'checksEmpty' => false],
        SmallerOrEqual::class => ['literal' => 0, 'checksEmpty' => true],
    ];

    private const array MIRRORED_OPERATORS = [
        Identical::class => Identical::class,
        Equal::class => Equal::class,
        NotIdentical::class => NotIdentical::class,
        NotEqual::class => NotEqual::class,
        Greater::class => Smaller::class,
        Smaller::class => Greater::class,
        GreaterOrEqual::class => SmallerOrEqual::class,
        SmallerOrEqual::class => GreaterOrEqual::class,
    ];

    public function matches(BinaryOp $comparison, string ...$functionNames): bool
    {
        return [] !== $this->findMatches($comparison, ...$functionNames);
    }

    public function match(BinaryOp $comparison, string ...$functionNames): EmptinessComparison
    {
        return $this->findMatches($comparison, ...$functionNames)[0] ?? throw new LogicException(self::NO_MATCH_MESSAGE);
    }

    /**
     * @return list<EmptinessComparison>
     */
    private function findMatches(BinaryOp $comparison, string ...$functionNames): array
    {
        return array_merge(
            $this->matchOriented($comparison->left, $comparison->right, $comparison::class, ...$functionNames),
            $this->matchOriented(
                $comparison->right,
                $comparison->left,
                self::MIRRORED_OPERATORS[$comparison::class] ?? $comparison::class,
                ...$functionNames,
            ),
        );
    }

    /**
     * @return list<EmptinessComparison>
     */
    private function matchOriented(Expr $callSide, Expr $literalSide, string $operator, string ...$functionNames): array
    {
        if (
            !array_key_exists($operator, self::CALL_ON_LEFT_FORMS)
            || !$this->isTargetCall($callSide, ...$functionNames)
            || !$this->isIntegerLiteral($literalSide, self::CALL_ON_LEFT_FORMS[$operator]['literal'])
        ) {
            return [];
        }

        return [new EmptinessComparison(
            $callSide,
            $callSide->getArgs()[0]->value,
            self::CALL_ON_LEFT_FORMS[$operator]['checksEmpty'],
        )];
    }

    /**
     * @phpstan-assert-if-true FuncCall $expression
     */
    private function isTargetCall(Expr $expression, string ...$functionNames): bool
    {
        return $expression instanceof FuncCall
            && $expression->name instanceof Name
            && in_array($expression->name->toLowerString(), $functionNames, true)
            && !$expression->isFirstClassCallable()
            && $this->hasPlainFirstArgument($expression);
    }

    private function hasPlainFirstArgument(FuncCall $call): bool
    {
        return [] !== $call->getArgs() && !$call->getArgs()[0]->unpack;
    }

    private function isIntegerLiteral(Expr $expression, int $value): bool
    {
        return $expression instanceof Int_ && $value === $expression->value;
    }
}
