<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Stmt\ClassConst;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

use function array_map;
use function array_values;
use function sprintf;

/**
 * @implements Rule<ClassConst>
 */
final class TypedClassConstantRule implements Rule
{
    private const string MESSAGE = 'Class constant %s must declare a type.';
    private const string IDENTIFIER = 'phpInspections.untypedClassConstant';

    public function getNodeType(): string
    {
        return ClassConst::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->isExempt($node, $scope)) {
            return [];
        }

        return array_values(array_map($this->buildError(...), $node->consts));
    }

    private function isExempt(ClassConst $classConstant, Scope $scope): bool
    {
        return null !== $classConstant->type || !$scope->getPhpVersion()->supportsNativeTypesInClassConstants()->yes();
    }

    private function buildError(Const_ $constant): IdentifierRuleError
    {
        return RuleErrorBuilder::message(sprintf(self::MESSAGE, $constant->name->toString()))
            ->identifier(self::IDENTIFIER)
            ->line($constant->getStartLine())
            ->build();
    }
}
