<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules;

use HiddenHint\PhpInspections\PHPStan\Rules\TypedClassConstantRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<TypedClassConstantRule>
 */
final class TypedClassConstantRuleTest extends RuleTestCase
{
    public function testReportsUntypedConstants(): void
    {
        $this->analyse([__DIR__ . '/data/typed-class-constant.php'], [
            ['Class constant LIMIT must declare a type.', 10],
            ['Class constant DEFAULT must declare a type.', 16],
        ]);
    }

    protected function getRule(): Rule
    {
        return new TypedClassConstantRule();
    }
}
