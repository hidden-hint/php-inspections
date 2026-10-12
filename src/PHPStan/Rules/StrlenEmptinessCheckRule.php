<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Name;
use PHPStan\Node\Printer\Printer;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

use function sprintf;

/**
 * @implements Rule<BinaryOp>
 */
final class StrlenEmptinessCheckRule implements Rule
{
    private const array FUNCTION_NAMES = ['strlen', 'mb_strlen'];
    private const string MESSAGE = 'Use \'\' %s %s instead of %s() to check emptiness.';
    private const string IDENTIFIER = 'phpInspections.strlenEmptinessCheck';
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
        if (!$this->emptinessComparisonMatcher->matches($node, ...self::FUNCTION_NAMES)) {
            return [];
        }

        return $this->reportStringSubject(
            $this->emptinessComparisonMatcher->match($node, ...self::FUNCTION_NAMES),
            $scope,
        );
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function reportStringSubject(EmptinessComparison $comparison, Scope $scope): array
    {
        if (!$scope->getType($comparison->subject)->isString()->yes()) {
            return [];
        }

        return [RuleErrorBuilder::message(sprintf(
            self::MESSAGE,
            $comparison->checksEmpty ? self::EMPTY_OPERATOR : self::NOT_EMPTY_OPERATOR,
            $this->printer->prettyPrintExpr($comparison->subject),
            $comparison->call->name instanceof Name ? $comparison->call->name->toString() : '',
        ))->identifier(self::IDENTIFIER)->build()];
    }
}
