<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules;

use HiddenHint\PhpInspections\PHPStan\Rules\CountEmptinessCheckRule;
use HiddenHint\PhpInspections\PHPStan\Rules\EmptinessComparisonMatcher;
use PHPStan\Node\Printer\Printer;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<CountEmptinessCheckRule>
 */
final class CountEmptinessCheckRuleTest extends RuleTestCase
{
    public function testReportsCountEmptinessChecksOnArrays(): void
    {
        $this->analyse([__DIR__ . '/data/count-emptiness.php'], [
            ['Use [] === $items instead of count() to check emptiness.', 14],
            ['Use [] !== $items instead of count() to check emptiness.', 15],
            ['Use [] !== $items instead of count() to check emptiness.', 16],
        ]);
    }

    protected function getRule(): Rule
    {
        return new CountEmptinessCheckRule(new EmptinessComparisonMatcher(), self::getContainer()->getByType(Printer::class));
    }
}
