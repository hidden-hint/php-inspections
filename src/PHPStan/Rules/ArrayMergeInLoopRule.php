<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\While_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\Printer\Printer;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

use function array_filter;
use function array_map;
use function array_values;
use function is_string;

/**
 * @implements Rule<Stmt>
 */
final class ArrayMergeInLoopRule implements Rule
{
    private const string FUNCTION_NAME = 'array_merge';
    private const string MESSAGE = 'Accumulating with array_merge() in a loop is quadratic; '
        . 'collect chunks and call array_merge(...$chunks) once.';
    private const string IDENTIFIER = 'phpInspections.arrayMergeInLoop';
    private const array BOUNDARIES = [
        Closure::class,
        ArrowFunction::class,
        Function_::class,
        Class_::class,
        Foreach_::class,
        For_::class,
        While_::class,
        Do_::class,
    ];

    public function __construct(
        private readonly BoundedNodeFinder $boundedNodeFinder,
        private readonly Printer $printer,
    ) {}

    public function getNodeType(): string
    {
        return Stmt::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        return array_map($this->buildError(...), $this->findAccumulations($this->loopBody($node)));
    }

    /**
     * @return list<Stmt>
     */
    private function loopBody(Stmt $loop): array
    {
        return $loop instanceof Foreach_ || $loop instanceof For_ || $loop instanceof While_ || $loop instanceof Do_
            ? array_values($loop->stmts)
            : [];
    }

    /**
     * @param list<Stmt> $body
     * @return list<Node>
     */
    private function findAccumulations(array $body): array
    {
        return $this->boundedNodeFinder->find($body, $this->isAccumulatingAssignment(...), ...self::BOUNDARIES);
    }

    private function isAccumulatingAssignment(Node $node): bool
    {
        return $node instanceof Assign && $this->isAccumulation($node);
    }

    private function isAccumulation(Assign $assign): bool
    {
        return $this->isAccumulator($assign->var)
            && $assign->expr instanceof FuncCall
            && $this->isArrayMerge($assign->expr)
            && $this->passesAccumulator($assign->expr, $this->printer->prettyPrintExpr($assign->var));
    }

    private function isAccumulator(Expr $target): bool
    {
        return ($target instanceof Variable && is_string($target->name))
            || $target instanceof PropertyFetch
            || $target instanceof StaticPropertyFetch
            || $target instanceof ArrayDimFetch;
    }

    private function isArrayMerge(FuncCall $call): bool
    {
        return $call->name instanceof Name && self::FUNCTION_NAME === $call->name->toLowerString();
    }

    private function passesAccumulator(FuncCall $call, string $printedAccumulator): bool
    {
        return [] !== array_filter(
            $call->getRawArgs(),
            fn(Node $argument): bool => $argument instanceof Arg
                && $printedAccumulator === $this->printer->prettyPrintExpr($argument->value),
        );
    }

    private function buildError(Node $accumulation): IdentifierRuleError
    {
        return RuleErrorBuilder::message(self::MESSAGE)
            ->identifier(self::IDENTIFIER)
            ->line($accumulation->getStartLine())
            ->build();
    }
}
