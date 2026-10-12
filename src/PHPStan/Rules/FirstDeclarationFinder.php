<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\StaticVar;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Global_;

use function array_filter;
use function array_map;
use function array_merge;
use function array_reduce;
use function array_values;
use function is_string;

final class FirstDeclarationFinder
{
    public function __construct(private readonly BoundedNodeFinder $boundedNodeFinder) {}

    /**
     * @return list<Variable>
     */
    public function inFunction(FunctionLike $function): array
    {
        return $this->firstPerName(
            ...$this->parameterVariables(...$function->getParams()),
            ...$this->statementDeclarations(...($function->getStmts() ?? [])),
        );
    }

    /**
     * @return list<Variable>
     */
    public function inStatements(Node ...$statements): array
    {
        return $this->firstPerName(...$this->statementDeclarations(...$statements));
    }

    /**
     * @return list<Variable>
     */
    private function parameterVariables(Param ...$parameters): array
    {
        return $this->namedVariables(...array_map(static fn(Param $parameter): Expr => $parameter->var, $parameters));
    }

    /**
     * @return list<Variable>
     */
    private function statementDeclarations(Node ...$statements): array
    {
        return array_merge([], ...array_map(
            $this->declaredVariables(...),
            $this->boundedNodeFinder->find(
                $statements,
                $this->isDeclaration(...),
                FunctionLike::class,
                ClassLike::class,
            ),
        ));
    }

    private function isDeclaration(Node $node): bool
    {
        return $node instanceof Assign
            || $node instanceof AssignRef
            || $node instanceof Foreach_
            || $node instanceof Catch_
            || $node instanceof StaticVar
            || $node instanceof Global_;
    }

    /**
     * @return list<Variable>
     */
    private function declaredVariables(Node $node): array
    {
        return match (true) {
            $node instanceof Assign, $node instanceof AssignRef => $this->namedVariables($node->var),
            $node instanceof Foreach_ => $this->namedVariables(...array_filter([$node->keyVar, $node->valueVar])),
            $node instanceof Catch_ => $this->namedVariables(...array_filter([$node->var])),
            $node instanceof StaticVar => $this->namedVariables($node->var),
            $node instanceof Global_ => $this->namedVariables(...$node->vars),
            default => [],
        };
    }

    /**
     * @return list<Variable>
     */
    private function namedVariables(Expr ...$targets): array
    {
        return array_merge([], ...array_map($this->targetVariables(...), array_values($targets)));
    }

    /**
     * @return list<Variable>
     */
    private function targetVariables(Expr $target): array
    {
        return match (true) {
            $target instanceof Variable && is_string($target->name) => [$target],
            $target instanceof Array_, $target instanceof List_ => $this->namedVariables(...array_map(
                static fn(ArrayItem $item): Expr => $item->value,
                array_filter($target->items),
            )),
            default => [],
        };
    }

    /**
     * @return list<Variable>
     */
    private function firstPerName(Variable ...$variables): array
    {
        return array_values(array_reduce($variables, $this->keepFirst(...), []));
    }

    /**
     * @param array<string, Variable> $firstByName
     * @return array<string, Variable>
     */
    private function keepFirst(array $firstByName, Variable $variable): array
    {
        return $firstByName + [is_string($variable->name) ? $variable->name : '' => $variable];
    }
}
