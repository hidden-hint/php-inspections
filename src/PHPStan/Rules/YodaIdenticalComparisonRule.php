<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
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

    public function __construct(private readonly YodaComparisonDetector $yodaComparisonDetector) {}

    public function getNodeType(): string
    {
        return Identical::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->yodaComparisonDetector->isNonYoda($node)) {
            return [];
        }

        return [RuleErrorBuilder::message(self::MESSAGE)->identifier(self::IDENTIFIER)->build()];
    }
}
