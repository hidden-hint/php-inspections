<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\PropertyItem;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

/**
 * @implements Rule<Node>
 */
final class AbbreviatedNameRule implements Rule
{
    public function __construct(
        private readonly FirstDeclarationFinder $firstDeclarationFinder,
        private readonly AbbreviatedNameReporter $abbreviatedNameReporter,
    ) {}

    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        return match (true) {
            $node instanceof PropertyItem => $this->abbreviatedNameReporter->report($node->name),
            $node instanceof FunctionLike => $this->abbreviatedNameReporter->report(
                ...$this->firstDeclarationFinder->inFunction($node),
            ),
            default => [],
        };
    }
}
