<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules;

use HiddenHint\PhpInspections\PHPStan\Rules\CountInForConditionRule;
use PhpParser\NodeFinder;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<CountInForConditionRule>
 */
final class CountInForConditionRuleTest extends RuleTestCase
{
    public function testReportsCallsInForCondition(): void
    {
        $this->analyse([__DIR__ . '/data/count-in-for-condition.php'], [
            ['Move count() out of the for loop condition; it is re-evaluated on every iteration.', 11],
            ['Move mb_strlen() out of the for loop condition; it is re-evaluated on every iteration.', 15],
        ]);
    }

    protected function getRule(): Rule
    {
        return new CountInForConditionRule(new NodeFinder());
    }
}
