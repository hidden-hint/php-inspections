<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\Rector\SharedConfig;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

use function copy;
use function dirname;
use function mkdir;
use function sys_get_temp_dir;
use function uniqid;

final class SharedConfigTest extends TestCase
{
    private const string FIXTURE_NAME = 'unrefactored.php';

    public function testEmptinessChecksAreRewrittenToYodaComparisons(): void
    {
        $workingDirectory = $this->createWorkingDirectory();
        copy(__DIR__ . '/fixtures/composer.json', $workingDirectory . '/composer.json');
        copy(__DIR__ . '/fixtures/' . self::FIXTURE_NAME, $workingDirectory . '/' . self::FIXTURE_NAME);

        $this->runRector($workingDirectory);

        self::assertFileEquals(__DIR__ . '/fixtures/refactored.php', $workingDirectory . '/' . self::FIXTURE_NAME);
    }

    private function createWorkingDirectory(): string
    {
        $workingDirectory = sys_get_temp_dir() . '/' . uniqid('php-inspections-rector-', true);
        mkdir($workingDirectory);

        return $workingDirectory;
    }

    private function runRector(string $workingDirectory): void
    {
        $projectRoot = dirname(__DIR__, 3);
        (new Process(
            [
                $projectRoot . '/vendor/bin/rector',
                'process',
                self::FIXTURE_NAME,
                '--config=' . $projectRoot . '/config/rector.php',
                '--no-progress-bar',
                '--clear-cache',
            ],
            $workingDirectory,
        ))->mustRun();
    }
}
