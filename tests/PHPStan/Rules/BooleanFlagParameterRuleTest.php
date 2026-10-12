<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules;

use HiddenHint\PhpInspections\PHPStan\Rules\BooleanFlagParameterRule;
use HiddenHint\PhpInspections\PHPStan\Rules\InheritedSignatureDetector;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<BooleanFlagParameterRule>
 */
final class BooleanFlagParameterRuleTest extends RuleTestCase
{
    public function testReportsBooleanFlags(): void
    {
        $this->analyse([__DIR__ . '/data/boolean-flag-parameter.php'], [
            ['Parameter $enabled is a boolean flag; split the method into two named methods.', 9],
            ['Parameter $force is a boolean flag; split the method into two named methods.', 12],
            ['Parameter $strict is a boolean flag; split the method into two named methods.', 18],
            ['Parameter $short is a boolean flag; split the method into two named methods.', 44],
        ]);
    }

    protected function getRule(): Rule
    {
        return new BooleanFlagParameterRule(new InheritedSignatureDetector());
    }
}
