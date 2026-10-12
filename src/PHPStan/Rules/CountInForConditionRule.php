<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\For_;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

use function array_filter;
use function array_map;
use function array_values;
use function in_array;
use function sprintf;

/**
 * @implements Rule<For_>
 */
final class CountInForConditionRule implements Rule
{
    private const array FUNCTION_NAMES = ['count', 'strlen', 'mb_strlen'];
    private const string MESSAGE = 'Move %s() out of the for loop condition; it is re-evaluated on every iteration.';
    private const string IDENTIFIER = 'phpInspections.callInForCondition';

    public function getNodeType(): string
    {
        return For_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        return array_map($this->buildError(...), $this->findMeasuringCalls($node));
    }

    /**
     * @return list<FuncCall>
     */
    private function findMeasuringCalls(For_ $loop): array
    {
        return array_values(array_filter(
            (new NodeFinder())->findInstanceOf($loop->cond, FuncCall::class),
            $this->isMeasuringCall(...),
        ));
    }

    private function isMeasuringCall(FuncCall $call): bool
    {
        return $call->name instanceof Name && in_array($call->name->toLowerString(), self::FUNCTION_NAMES, true);
    }

    private function buildError(FuncCall $call): IdentifierRuleError
    {
        return RuleErrorBuilder::message(sprintf(self::MESSAGE, $call->name instanceof Name ? $call->name->toString() : ''))
            ->identifier(self::IDENTIFIER)
            ->line($call->getStartLine())
            ->build();
    }
}
