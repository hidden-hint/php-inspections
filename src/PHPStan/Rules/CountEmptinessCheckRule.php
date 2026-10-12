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
        if (!$this->emptinessComparisonMatcher->matches($node, self::FUNCTION_NAME)) {
            return [];
        }

        return $this->reportArraySubject($this->emptinessComparisonMatcher->match($node, self::FUNCTION_NAME), $scope);
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function reportArraySubject(EmptinessComparison $comparison, Scope $scope): array
    {
        if (!$scope->getNativeType($comparison->subject)->isArray()->yes()) {
            return [];
        }

        return [RuleErrorBuilder::message(sprintf(
            self::MESSAGE,
            $comparison->checksEmpty ? self::EMPTY_OPERATOR : self::NOT_EMPTY_OPERATOR,
            $this->printer->prettyPrintExpr($comparison->subject),
        ))->identifier(self::IDENTIFIER)->build()];
    }
}
