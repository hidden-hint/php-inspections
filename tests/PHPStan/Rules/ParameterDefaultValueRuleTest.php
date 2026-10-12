<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan\Rules;

use HiddenHint\PhpInspections\PHPStan\Rules\InheritedSignatureDetector;
use HiddenHint\PhpInspections\PHPStan\Rules\ParameterDefaultValueRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ParameterDefaultValueRule>
 */
final class ParameterDefaultValueRuleTest extends RuleTestCase
{
    public function testReportsDefaults(): void
    {
        $this->analyse([__DIR__ . '/data/parameter-default-value.php'], [
            ['Parameter $limit must not have a default value.', 14],
            ['Parameter $limit must not have a default value.', 16],
        ]);
    }

    protected function getRule(): Rule
    {
        return new ParameterDefaultValueRule(new InheritedSignatureDetector());
    }
}
