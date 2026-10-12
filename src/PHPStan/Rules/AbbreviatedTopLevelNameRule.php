<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FileNode;
use PHPStan\Rules\Rule;

/**
 * @implements Rule<FileNode>
 */
final class AbbreviatedTopLevelNameRule implements Rule
{
    public function __construct(
        private readonly FirstDeclarationFinder $firstDeclarationFinder,
        private readonly AbbreviatedNameReporter $abbreviatedNameReporter,
    ) {}

    public function getNodeType(): string
    {
        return FileNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        return $this->abbreviatedNameReporter->report(
            ...$this->firstDeclarationFinder->inStatements(...$node->getNodes()),
        );
    }
}
