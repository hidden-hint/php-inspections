<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules;

use HiddenHint\PhpInspections\PHPStan\Rules\ArrayMergeInLoopRule;
use PhpParser\NodeFinder;
use PHPStan\Node\Printer\Printer;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ArrayMergeInLoopRule>
 */
final class ArrayMergeInLoopRuleTest extends RuleTestCase
{
    private const string MESSAGE = 'Accumulating with array_merge() in a loop is quadratic; '
        . 'collect chunks and call array_merge(...$chunks) once.';

    public function testReportsAccumulationOnly(): void
    {
        $this->analyse([__DIR__ . '/data/array-merge-in-loop.php'], [
            [self::MESSAGE, 13],
            [self::MESSAGE, 27],
            [self::MESSAGE, 44],
            [self::MESSAGE, 45],
            [self::MESSAGE, 49],
        ]);
    }

    protected function getRule(): Rule
    {
        return new ArrayMergeInLoopRule(new NodeFinder(), self::getContainer()->getByType(Printer::class));
    }
}
