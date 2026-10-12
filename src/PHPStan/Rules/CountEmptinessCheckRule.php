<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PHPStan\Node\Printer\Printer;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

use function array_filter;
use function array_map;
use function array_values;
use function sprintf;

/**
 * @implements Rule<BinaryOp>
 */
final class CountEmptinessCheckRule implements Rule
{
    private const string FUNCTION_NAME = 'count';
    private const string MESSAGE = 'Use [] %s %s instead of count() to check emptiness.';
    private const string IDENTIFIER = 'phpInspections.countEmptinessCheck';
    private const string EMPTY_OPERATOR = '===';
    private const string NOT_EMPTY_OPERATOR = '!==';

    public function __construct(
        private readonly EmptinessComparisonMatcher $emptinessComparisonMatcher,
        private readonly Printer $printer,
    ) {}

    public function getNodeType(): string
    {
        return BinaryOp::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        return array_map($this->buildError(...), array_values(array_filter(
            $this->emptinessComparisonMatcher->match($node, self::FUNCTION_NAME),
            static fn(EmptinessComparison $comparison): bool => $scope->getNativeType($comparison->subject)
                ->isArray()
                ->yes(),
        )));
    }

    private function buildError(EmptinessComparison $comparison): IdentifierRuleError
    {
        return RuleErrorBuilder::message(sprintf(
            self::MESSAGE,
            $comparison->checksEmpty ? self::EMPTY_OPERATOR : self::NOT_EMPTY_OPERATOR,
            $this->printer->prettyPrintExpr($comparison->subject),
        ))->identifier(self::IDENTIFIER)->build();
    }
}
