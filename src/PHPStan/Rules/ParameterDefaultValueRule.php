<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

use function array_filter;
use function array_map;
use function array_values;
use function is_string;
use function sprintf;

/**
 * @implements Rule<FunctionLike>
 */
final class ParameterDefaultValueRule implements Rule
{
    private const string MESSAGE = 'Parameter $%s must not have a default value.';
    private const string IDENTIFIER = 'phpInspections.parameterDefaultValue';

    public function __construct(private readonly InheritedSignatureDetector $inheritedSignatureDetector) {}

    public function getNodeType(): string
    {
        return FunctionLike::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        return array_map(
            $this->buildError(...),
            array_values(array_filter($this->authoredParameters($node, $scope), $this->hasDefault(...))),
        );
    }

    /**
     * @return array<Param>
     */
    private function authoredParameters(FunctionLike $node, Scope $scope): array
    {
        return match (true) {
            $node instanceof Function_ => $node->params,
            $node instanceof ClassMethod && !$this->inheritedSignatureDetector->isInherited($node, $scope) => $node->params,
            default => [],
        };
    }

    private function hasDefault(Param $parameter): bool
    {
        return null !== $parameter->default;
    }

    private function buildError(Param $parameter): IdentifierRuleError
    {
        return RuleErrorBuilder::message(sprintf(self::MESSAGE, $this->parameterName($parameter)))
            ->identifier(self::IDENTIFIER)
            ->line($parameter->getStartLine())
            ->build();
    }

    private function parameterName(Param $parameter): string
    {
        return $parameter->var instanceof Variable && is_string($parameter->var->name) ? $parameter->var->name : '';
    }
}
