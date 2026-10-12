<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

use function array_filter;
use function array_map;
use function array_merge;
use function array_values;
use function sprintf;
use function str_starts_with;

/**
 * @implements Rule<Property>
 */
final class MemoizedPropertyRule implements Rule
{
    private const string PREFIX = 'memoized';
    private const string NULL_TYPE = 'null';
    private const string UNTYPED_MESSAGE = 'Memoized property $%s must declare a type.';
    private const string UNTYPED_IDENTIFIER = 'phpInspections.memoizedPropertyUntyped';
    private const string NULLABLE_MESSAGE = 'Memoized property $%s must not be nullable.';
    private const string NULLABLE_IDENTIFIER = 'phpInspections.memoizedPropertyNullable';
    private const string DEFAULT_MESSAGE = 'Memoized property $%s must not have a default value.';
    private const string DEFAULT_IDENTIFIER = 'phpInspections.memoizedPropertyDefault';

    public function getNodeType(): string
    {
        return Property::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        return array_merge([], ...array_map(
            fn(PropertyItem $item): array => $this->inspect($node, $item),
            array_values(array_filter($node->props, $this->isMemoized(...))),
        ));
    }

    private function isMemoized(PropertyItem $item): bool
    {
        return str_starts_with($item->name->toString(), self::PREFIX);
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function inspect(Property $property, PropertyItem $item): array
    {
        return array_merge(
            $this->reportUntyped($property, $item),
            $this->reportNullable($property, $item),
            $this->reportDefault($item),
        );
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function reportUntyped(Property $property, PropertyItem $item): array
    {
        if (null !== $property->type) {
            return [];
        }

        return [$this->buildError(self::UNTYPED_MESSAGE, self::UNTYPED_IDENTIFIER, $item)];
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function reportNullable(Property $property, PropertyItem $item): array
    {
        if (null === $property->type || !$this->isNullable($property->type)) {
            return [];
        }

        return [$this->buildError(self::NULLABLE_MESSAGE, self::NULLABLE_IDENTIFIER, $item)];
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function reportDefault(PropertyItem $item): array
    {
        if (null === $item->default) {
            return [];
        }

        return [$this->buildError(self::DEFAULT_MESSAGE, self::DEFAULT_IDENTIFIER, $item)];
    }

    private function isNullable(Identifier|Name|ComplexType $type): bool
    {
        return $type instanceof NullableType
            || ($type instanceof UnionType && [] !== array_filter($type->types, $this->isNullType(...)))
            || $this->isNullType($type);
    }

    private function isNullType(Node $type): bool
    {
        return $type instanceof Identifier && self::NULL_TYPE === $type->toLowerString();
    }

    private function buildError(string $message, string $identifier, PropertyItem $item): IdentifierRuleError
    {
        return RuleErrorBuilder::message(sprintf($message, $item->name->toString()))
            ->identifier($identifier)
            ->line($item->getStartLine())
            ->build();
    }
}
