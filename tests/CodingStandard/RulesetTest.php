<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\CodingStandard;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

use function copy;
use function dirname;
use function mkdir;
use function sys_get_temp_dir;
use function uniqid;

final class RulesetTest extends TestCase
{
    private const string FIXTURE_NAME = 'unformatted.php';

    public function testFormatsFixtureToExpectedOutput(): void
    {
        $workingDirectory = $this->createWorkingDirectory();
        copy(__DIR__ . '/fixtures/' . self::FIXTURE_NAME, $workingDirectory . '/' . self::FIXTURE_NAME);

        $this->runFixer($workingDirectory);

        self::assertFileEquals(__DIR__ . '/fixtures/formatted.php', $workingDirectory . '/' . self::FIXTURE_NAME);
    }

    private function createWorkingDirectory(): string
    {
        $workingDirectory = sys_get_temp_dir() . '/' . uniqid('php-inspections-ruleset-', true);
        mkdir($workingDirectory);

        return $workingDirectory;
    }

    private function runFixer(string $workingDirectory): void
    {
        $projectRoot = dirname(__DIR__, 2);
        (new Process([
            $projectRoot . '/vendor/bin/php-cs-fixer',
            'fix',
            '--config=' . $projectRoot . '/config/php-cs-fixer.php',
            '--using-cache=no',
            $workingDirectory,
        ]))->mustRun();
    }
}
