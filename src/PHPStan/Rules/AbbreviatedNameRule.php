<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\VarLikeIdentifier;
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
 * @implements Rule<Node>
 */
final class AbbreviatedNameRule implements Rule
{
    private const string MESSAGE = 'Name $%s is an abbreviation; use a full descriptive word.';
    private const string IDENTIFIER = 'phpInspections.abbreviatedName';

    /**
     * @var list<string>
     */
    private readonly array $abbreviations;

    public function __construct(string ...$abbreviations)
    {
        $this->abbreviations = array_values($abbreviations);
    }

    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        return array_map(
            $this->buildError(...),
            array_values(array_filter($this->declaredNames($node, $scope), $this->isAbbreviation(...))),
        );
    }

    /**
     * @return list<Variable|VarLikeIdentifier>
     */
    private function declaredNames(Node $node, Scope $scope): array
    {
        return match (true) {
            $node instanceof Param => $this->namedVariables($node->var),
            $node instanceof PropertyItem => [$node->name],
            $node instanceof Catch_ => $this->namedVariables(...array_filter([$node->var])),
            $node instanceof Foreach_ => $this->namedVariables(...array_filter([$node->keyVar, $node->valueVar])),
            $node instanceof Assign => $this->firstAssignedVariables($node, $scope),
            default => [],
        };
    }

    /**
     * @return list<Variable>
     */
    private function firstAssignedVariables(Assign $assign, Scope $scope): array
    {
        return array_values(array_filter(
            $this->namedVariables($assign->var),
            static fn(Variable $variable): bool => is_string($variable->name)
                && !$scope->hasVariableType($variable->name)->yes(),
        ));
    }

    /**
     * @return list<Variable>
     */
    private function namedVariables(Expr ...$expressions): array
    {
        return array_values(array_filter($expressions, $this->isNamedVariable(...)));
    }

    /**
     * @phpstan-assert-if-true Variable $expression
     */
    private function isNamedVariable(Expr $expression): bool
    {
        return $expression instanceof Variable && is_string($expression->name);
    }

    private function isAbbreviation(Variable|VarLikeIdentifier $name): bool
    {
        return in_array($this->nameOf($name), $this->abbreviations, true);
    }

    private function nameOf(Variable|VarLikeIdentifier $name): string
    {
        return $name instanceof VarLikeIdentifier ? $name->toString() : (is_string($name->name) ? $name->name : '');
    }

    private function buildError(Variable|VarLikeIdentifier $name): IdentifierRuleError
    {
        return RuleErrorBuilder::message(sprintf(self::MESSAGE, $this->nameOf($name)))
            ->identifier(self::IDENTIFIER)
            ->line($name->getStartLine())
            ->build();
    }
}
