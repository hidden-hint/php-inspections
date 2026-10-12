<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules;

use HiddenHint\PhpInspections\PHPStan\Rules\AbbreviatedNameReporter;
use HiddenHint\PhpInspections\PHPStan\Rules\AbbreviatedNameRule;
use HiddenHint\PhpInspections\PHPStan\Rules\AbbreviationDenylist;
use HiddenHint\PhpInspections\PHPStan\Rules\BoundedNodeFinder;
use HiddenHint\PhpInspections\PHPStan\Rules\FirstDeclarationFinder;
use PhpParser\NodeFinder;
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
        $this->rule = $this->createRule('buf', 'cfg', 'e', 'k', 'v', 'tmp');

        $this->analyse([__DIR__ . '/data/abbreviated-name.php'], [
            [sprintf(self::MESSAGE, 'buf'), 11],
            [sprintf(self::MESSAGE, 'cfg'), 13],
            [sprintf(self::MESSAGE, 'e'), 19],
            [sprintf(self::MESSAGE, 'k'), 21],
            [sprintf(self::MESSAGE, 'v'), 21],
            [sprintf(self::MESSAGE, 'tmp'), 23],
        ]);
    }

    public function testReportsFirstDeclarationPerFunctionScope(): void
    {
        $this->rule = $this->createRule('k', 'v', 'cfg', 'idx', 'val', 'tmp', 'buf', 'res');

        $this->analyse([__DIR__ . '/data/abbreviated-name-declarations.php'], [
            [sprintf(self::MESSAGE, 'k'), 11],
            [sprintf(self::MESSAGE, 'v'), 11],
            [sprintf(self::MESSAGE, 'cfg'), 12],
            [sprintf(self::MESSAGE, 'idx'), 13],
            [sprintf(self::MESSAGE, 'val'), 13],
            [sprintf(self::MESSAGE, 'tmp'), 15],
            [sprintf(self::MESSAGE, 'buf'), 17],
            [sprintf(self::MESSAGE, 'res'), 21],
            [sprintf(self::MESSAGE, 'buf'), 26],
        ]);
    }

    public function testRespectsCustomList(): void
    {
        $this->rule = $this->createRule('sku');

        $this->analyse([__DIR__ . '/data/abbreviated-name.php'], [
            [sprintf(self::MESSAGE, 'sku'), 18],
        ]);
    }

    protected function getRule(): Rule
    {
        return $this->rule;
    }

    private function createRule(string ...$abbreviations): AbbreviatedNameRule
    {
        return new AbbreviatedNameRule(
            new FirstDeclarationFinder(new BoundedNodeFinder(new NodeFinder())),
            new AbbreviatedNameReporter(new AbbreviationDenylist(...$abbreviations)),
        );
    }
}
