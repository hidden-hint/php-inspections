<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules;

use HiddenHint\PhpInspections\PHPStan\Rules\YodaComparisonDetector;
use HiddenHint\PhpInspections\PHPStan\Rules\YodaIdenticalComparisonRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<YodaIdenticalComparisonRule>
 */
final class YodaIdenticalComparisonRuleTest extends RuleTestCase
{
    private const string MESSAGE = 'Use Yoda notation: put the constant operand on the left side of ===.';

    public function testReportsNonYodaIdenticalComparisons(): void
    {
        $this->analyse([__DIR__ . '/data/yoda-identical-comparison.php'], [
            [self::MESSAGE, 14],
            [self::MESSAGE, 16],
            [self::MESSAGE, 17],
            [self::MESSAGE, 21],
        ]);
    }

    protected function getRule(): Rule
    {
        return new YodaIdenticalComparisonRule(new YodaComparisonDetector());
    }
}
