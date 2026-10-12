<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\UnionType;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

use function array_filter;
use function array_map;
use function array_values;
use function in_array;
use function is_string;
use function sprintf;

/**
 * @implements Rule<FunctionLike>
 */
final class BooleanFlagParameterRule implements Rule
{
    private const string MESSAGE = 'Parameter $%s is a boolean flag; split the method into two named methods.';
    private const string IDENTIFIER = 'phpInspections.booleanFlagParameter';
    private const array BOOLEAN_TYPES = ['bool', 'true', 'false'];
    private const array BOOLEAN_UNION_MEMBER_TYPES = ['bool', 'true', 'false', 'null'];

    public function __construct(private readonly InheritedSignatureDetector $inheritedSignatureDetector) {}

    public function getNodeType(): string
    {
        return FunctionLike::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        return array_map(
            $this->buildError(...),
            array_values(array_filter($this->authoredParameters($node, $scope), $this->isBooleanFlag(...))),
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

    private function isBooleanFlag(Param $parameter): bool
    {
        return !$parameter->isPromoted() && null !== $parameter->type && $this->isBooleanType($parameter->type);
    }

    private function isBooleanType(Node $type): bool
    {
        return match (true) {
            $type instanceof NullableType => $this->isBooleanType($type->type),
            $type instanceof UnionType => [] === array_filter($type->types, $this->isNotBooleanUnionMember(...)),
            $type instanceof Identifier => in_array($type->toLowerString(), self::BOOLEAN_TYPES, true),
            default => false,
        };
    }

    private function isNotBooleanUnionMember(Node $type): bool
    {
        return !$type instanceof Identifier || !in_array($type->toLowerString(), self::BOOLEAN_UNION_MEMBER_TYPES, true);
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
