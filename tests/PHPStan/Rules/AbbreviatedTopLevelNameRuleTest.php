<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules;

use HiddenHint\PhpInspections\PHPStan\Rules\AbbreviatedNameReporter;
use HiddenHint\PhpInspections\PHPStan\Rules\AbbreviatedTopLevelNameRule;
use HiddenHint\PhpInspections\PHPStan\Rules\AbbreviationDenylist;
use HiddenHint\PhpInspections\PHPStan\Rules\BoundedNodeFinder;
use HiddenHint\PhpInspections\PHPStan\Rules\FirstDeclarationFinder;
use PhpParser\NodeFinder;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

use function sprintf;

/**
 * @extends RuleTestCase<AbbreviatedTopLevelNameRule>
 */
final class AbbreviatedTopLevelNameRuleTest extends RuleTestCase
{
    private const string MESSAGE = 'Name $%s is an abbreviation; use a full descriptive word.';

    public function testReportsFirstTopLevelDeclarationOutsideFunctions(): void
    {
        $this->analyse([__DIR__ . '/data/abbreviated-name-top-level.php'], [
            [sprintf(self::MESSAGE, 'tmp'), 5],
            [sprintf(self::MESSAGE, 'val'), 7],
        ]);
    }

    protected function getRule(): Rule
    {
        return new AbbreviatedTopLevelNameRule(
            new FirstDeclarationFinder(new BoundedNodeFinder(new NodeFinder())),
            new AbbreviatedNameReporter(new AbbreviationDenylist('tmp', 'val', 'res')),
        );
    }
}
