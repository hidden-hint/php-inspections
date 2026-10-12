<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\Rector\CountEmptinessCheckToArrayComparisonRector;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CountEmptinessCheckToArrayComparisonRectorTest extends AbstractRectorTestCase
{
    #[DataProvider('provideFixtures')]
    public function testRefactorsFixture(string $fixturePath): void
    {
        $this->doTestFile($fixturePath);
    }

    /**
     * @return Iterator<array<string>>
     */
    public static function provideFixtures(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}
