<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules;

use HiddenHint\PhpInspections\PHPStan\Rules\TypedClassConstantRule;
use PHPStan\Php\PhpVersion;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<TypedClassConstantRule>
 */
final class TypedClassConstantRuleTest extends RuleTestCase
{
    private const int PHP_8_2 = 80200;
    private const int PHP_8_3 = 80300;

    private PhpVersion $phpVersion;

    public function testReportsUntypedConstants(): void
    {
        $this->phpVersion = new PhpVersion(self::PHP_8_3);

        $this->analyse([__DIR__ . '/data/typed-class-constant.php'], [
            ['Class constant LIMIT must declare a type.', 10],
            ['Class constant DEFAULT must declare a type.', 16],
        ]);
    }

    public function testSilentBelowPhp83(): void
    {
        $this->phpVersion = new PhpVersion(self::PHP_8_2);

        $this->analyse([__DIR__ . '/data/typed-class-constant.php'], []);
    }

    protected function getRule(): Rule
    {
        return new TypedClassConstantRule($this->phpVersion);
    }
}
