<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules;

use HiddenHint\PhpInspections\PHPStan\Rules\EmptinessComparisonMatcher;
use HiddenHint\PhpInspections\PHPStan\Rules\StrlenEmptinessCheckRule;
use PHPStan\Node\Printer\Printer;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<StrlenEmptinessCheckRule>
 */
final class StrlenEmptinessCheckRuleTest extends RuleTestCase
{
    public function testReportsStrlenEmptinessChecksOnNonNullableStrings(): void
    {
        $this->analyse([__DIR__ . '/data/strlen-emptiness.php'], [
            ['Use \'\' === $name instead of strlen() to check emptiness.', 12],
            ['Use \'\' !== $name instead of mb_strlen() to check emptiness.', 13],
        ]);
    }

    protected function getRule(): Rule
    {
        return new StrlenEmptinessCheckRule(new EmptinessComparisonMatcher(), self::getContainer()->getByType(Printer::class));
    }
}
