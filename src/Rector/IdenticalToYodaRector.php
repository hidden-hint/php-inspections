<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Rector;

use HiddenHint\PhpInspections\PHPStan\Rules\YodaComparisonDetector;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use Rector\Rector\AbstractRector;

final class IdenticalToYodaRector extends AbstractRector
{
    public function __construct(private readonly YodaComparisonDetector $yodaComparisonDetector) {}

    public function getNodeTypes(): array
    {
        return [Identical::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof Identical || !$this->yodaComparisonDetector->isNonYoda($node)) {
            return null;
        }

        return new Identical($node->right, $node->left);
    }
}
