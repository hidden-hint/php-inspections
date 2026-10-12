<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Rector;

use HiddenHint\PhpInspections\PHPStan\Rules\EmptinessComparison;
use HiddenHint\PhpInspections\PHPStan\Rules\EmptinessComparisonMatcher;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;

use function array_filter;
use function array_map;
use function array_values;

final class StrlenEmptinessCheckToStringComparisonRector extends AbstractRector
{
    private const array FUNCTION_NAMES = ['strlen', 'mb_strlen'];

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

        return array_map($this->toStringComparison(...), array_values(array_filter(
            $this->emptinessComparisonMatcher->match($node, ...self::FUNCTION_NAMES),
            $this->hasStringSubject(...),
        )))[0] ?? null;
    }

    private function hasStringSubject(EmptinessComparison $comparison): bool
    {
        return $this->nodeTypeResolver->getNativeType($comparison->subject)->isString()->yes();
    }

    private function toStringComparison(EmptinessComparison $comparison): Identical|NotIdentical
    {
        return $comparison->checksEmpty
            ? new Identical(new String_(''), $comparison->subject)
            : new NotIdentical(new String_(''), $comparison->subject);
    }
}
