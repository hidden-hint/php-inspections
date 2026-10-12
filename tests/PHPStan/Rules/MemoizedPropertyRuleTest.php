<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules;

use HiddenHint\PhpInspections\PHPStan\Rules\MemoizedPropertyRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<MemoizedPropertyRule>
 */
final class MemoizedPropertyRuleTest extends RuleTestCase
{
    public function testReportsInvalidMemoizedProperties(): void
    {
        $this->analyse([__DIR__ . '/data/memoized-property.php'], [
            ['Memoized property $memoizedItems must not be nullable.', 9],
            ['Memoized property $memoizedItems must not have a default value.', 9],
            ['Memoized property $memoizedTotal must declare a type.', 10],
        ]);
    }

    protected function getRule(): Rule
    {
        return new MemoizedPropertyRule();
    }
}
