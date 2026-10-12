<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Rector;

use HiddenHint\PhpInspections\PHPStan\Rules\EmptinessComparison;
use HiddenHint\PhpInspections\PHPStan\Rules\EmptinessComparisonMatcher;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Rector\Rector\AbstractRector;

final class CountEmptinessCheckToArrayComparisonRector extends AbstractRector
{
    private const string FUNCTION_NAME = 'count';
    private const string ARRAY_KIND_ATTRIBUTE = 'kind';

    public function __construct(private readonly EmptinessComparisonMatcher $emptinessComparisonMatcher) {}

    public function getNodeTypes(): array
    {
        return [BinaryOp::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof BinaryOp || !$this->isArrayEmptinessCheck($node)) {
            return null;
        }

        return $this->toArrayComparison($this->emptinessComparisonMatcher->match($node, self::FUNCTION_NAME));
    }

    private function isArrayEmptinessCheck(BinaryOp $comparison): bool
    {
        return $this->emptinessComparisonMatcher->matches($comparison, self::FUNCTION_NAME)
            && $this->nodeTypeResolver
                ->getNativeType($this->emptinessComparisonMatcher->match($comparison, self::FUNCTION_NAME)->subject)
                ->isArray()
                ->yes();
    }

    private function toArrayComparison(EmptinessComparison $comparison): Identical|NotIdentical
    {
        return $comparison->checksEmpty
            ? new Identical($this->createEmptyArray(), $comparison->subject)
            : new NotIdentical($this->createEmptyArray(), $comparison->subject);
    }

    private function createEmptyArray(): Array_
    {
        return new Array_([], [self::ARRAY_KIND_ATTRIBUTE => Array_::KIND_SHORT]);
    }
}
