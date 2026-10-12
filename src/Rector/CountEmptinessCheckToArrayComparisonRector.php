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

use function array_filter;
use function array_map;
use function array_values;

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
        if (!$node instanceof BinaryOp) {
            return null;
        }

        return array_map($this->toArrayComparison(...), array_values(array_filter(
            $this->emptinessComparisonMatcher->match($node, self::FUNCTION_NAME),
            $this->hasArraySubject(...),
        )))[0] ?? null;
    }

    private function hasArraySubject(EmptinessComparison $comparison): bool
    {
        return $this->nodeTypeResolver->getNativeType($comparison->subject)->isArray()->yes();
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
