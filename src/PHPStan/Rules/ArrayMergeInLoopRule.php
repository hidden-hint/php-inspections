<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\While_;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

use function array_filter;
use function array_map;
use function array_values;
use function in_array;
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

    public function __construct(private readonly NodeFinder $nodeFinder) {}

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
     * @return list<Assign>
     */
    private function findAccumulations(array $body): array
    {
        $nestedAccumulations = $this->accumulationsWithin(...$this->nodeFinder->find($body, $this->isBoundary(...)));

        return array_values(array_filter(
            $this->accumulationsWithin(...$body),
            static fn(Assign $assign): bool => !in_array($assign, $nestedAccumulations, true),
        ));
    }

    /**
     * @return list<Assign>
     */
    private function accumulationsWithin(Node ...$nodes): array
    {
        return array_values(array_filter(
            $this->nodeFinder->findInstanceOf(array_values($nodes), Assign::class),
            $this->isAccumulation(...),
        ));
    }

    private function isBoundary(Node $node): bool
    {
        return [] !== array_filter(self::BOUNDARIES, static fn(string $boundary): bool => $node instanceof $boundary);
    }

    private function isAccumulation(Assign $assign): bool
    {
        return $assign->var instanceof Variable
            && is_string($assign->var->name)
            && $assign->expr instanceof FuncCall
            && $this->isArrayMerge($assign->expr)
            && $this->passesVariable($assign->expr, $assign->var->name);
    }

    private function isArrayMerge(FuncCall $call): bool
    {
        return $call->name instanceof Name && self::FUNCTION_NAME === $call->name->toLowerString();
    }

    private function passesVariable(FuncCall $call, string $variableName): bool
    {
        return [] !== array_filter(
            $call->getRawArgs(),
            static fn(Node $argument): bool => $argument instanceof Arg
                && $argument->value instanceof Variable
                && $variableName === $argument->value->name,
        );
    }

    private function buildError(Assign $assign): IdentifierRuleError
    {
        return RuleErrorBuilder::message(self::MESSAGE)
            ->identifier(self::IDENTIFIER)
            ->line($assign->getStartLine())
            ->build();
    }
}
