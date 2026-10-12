<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules;

use HiddenHint\PhpInspections\PHPStan\Rules\AbbreviatedNameRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

use function sprintf;

/**
 * @extends RuleTestCase<AbbreviatedNameRule>
 */
final class AbbreviatedNameRuleTest extends RuleTestCase
{
    private const string MESSAGE = 'Name $%s is an abbreviation; use a full descriptive word.';

    private AbbreviatedNameRule $rule;

    public function testReportsDenylistedNames(): void
    {
        $this->rule = new AbbreviatedNameRule('buf', 'cfg', 'e', 'k', 'v', 'tmp');

        $this->analyse([__DIR__ . '/data/abbreviated-name.php'], [
            [sprintf(self::MESSAGE, 'buf'), 11],
            [sprintf(self::MESSAGE, 'cfg'), 13],
            [sprintf(self::MESSAGE, 'e'), 19],
            [sprintf(self::MESSAGE, 'k'), 21],
            [sprintf(self::MESSAGE, 'v'), 21],
            [sprintf(self::MESSAGE, 'tmp'), 23],
        ]);
    }

    public function testRespectsCustomList(): void
    {
        $this->rule = new AbbreviatedNameRule('sku');

        $this->analyse([__DIR__ . '/data/abbreviated-name.php'], [
            [sprintf(self::MESSAGE, 'sku'), 18],
        ]);
    }

    protected function getRule(): Rule
    {
        return $this->rule;
    }
}
