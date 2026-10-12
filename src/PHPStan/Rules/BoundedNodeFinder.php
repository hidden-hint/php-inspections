<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\NodeFinder;

use function array_filter;
use function array_values;
use function in_array;

final class BoundedNodeFinder
{
    public function __construct(private readonly NodeFinder $nodeFinder) {}

    /**
     * @param array<Node> $nodes
     * @param callable(Node): bool $filter
     * @param class-string<Node> ...$boundaries
     * @return list<Node>
     */
    public function find(array $nodes, callable $filter, string ...$boundaries): array
    {
        $nestedMatches = $this->nodeFinder->find(
            $this->nodeFinder->find($nodes, $this->isBoundary(...$boundaries)),
            $filter,
        );

        return array_values(array_filter(
            $this->nodeFinder->find($nodes, $filter),
            static fn(Node $match): bool => !in_array($match, $nestedMatches, true),
        ));
    }

    /**
     * @param class-string<Node> ...$boundaries
     * @return callable(Node): bool
     */
    private function isBoundary(string ...$boundaries): callable
    {
        return static fn(Node $node): bool => [] !== array_filter(
            $boundaries,
            static fn(string $boundary): bool => $node instanceof $boundary,
        );
    }
}
