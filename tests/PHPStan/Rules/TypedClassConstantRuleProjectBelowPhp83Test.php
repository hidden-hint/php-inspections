<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules;

use HiddenHint\PhpInspections\PHPStan\Rules\TypedClassConstantRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<TypedClassConstantRule>
 */
final class TypedClassConstantRuleProjectBelowPhp83Test extends RuleTestCase
{
    public function testSilentWhenProjectAllowsPhpBelow83(): void
    {
        $this->analyse([__DIR__ . '/data/typed-class-constant.php'], []);
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [...parent::getAdditionalConfigFiles(), __DIR__ . '/config/php-8.2-to-8.3.neon'];
    }

    protected function getRule(): Rule
    {
        return new TypedClassConstantRule();
    }
}
