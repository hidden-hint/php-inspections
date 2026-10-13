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

    private const string TEMPLATE_NAME = 'template.phtml';

    private const string FILE_DOCBLOCK_NAME = 'file-docblock.php';

    private const string FORMAT_CONFIG = 'php-cs-fixer.php';

    private const string REFACTOR_CONFIG = 'php-cs-fixer-refactor.php';

    public function testFormatConfigOnlyChangesLayout(): void
    {
        $fixturePath = $this->copyFixture(self::FIXTURE_NAME);

        $this->runFixer(self::FORMAT_CONFIG, $fixturePath);

        self::assertFileEquals(__DIR__ . '/fixtures/formatted.php', $fixturePath);
    }

    public function testRefactorConfigAppliesRiskyRules(): void
    {
        $fixturePath = $this->copyFixture(self::FIXTURE_NAME);

        $this->runFixer(self::REFACTOR_CONFIG, $fixturePath);

        self::assertFileEquals(__DIR__ . '/fixtures/refactored.php', $fixturePath);
    }

    public function testKeepsSpaceBeforeClosingTagAfterSingleLineComment(): void
    {
        $templatePath = $this->copyFixture(self::TEMPLATE_NAME);

        $this->runFixer(self::FORMAT_CONFIG, $templatePath);

        self::assertFileEquals(__DIR__ . '/fixtures/' . self::TEMPLATE_NAME, $templatePath);
    }

    public function testKeepsFileDocBlockDirectlyAfterOpeningTag(): void
    {
        $fixturePath = $this->copyFixture(self::FILE_DOCBLOCK_NAME);

        $this->runFixer(self::FORMAT_CONFIG, $fixturePath);

        self::assertFileEquals(__DIR__ . '/fixtures/' . self::FILE_DOCBLOCK_NAME, $fixturePath);
    }

    private function copyFixture(string $fixtureName): string
    {
        $fixturePath = $this->createWorkingDirectory() . '/' . $fixtureName;
        copy(__DIR__ . '/fixtures/' . $fixtureName, $fixturePath);

        return $fixturePath;
    }

    private function createWorkingDirectory(): string
    {
        $workingDirectory = sys_get_temp_dir() . '/' . uniqid('php-inspections-ruleset-', true);
        mkdir($workingDirectory);

        return $workingDirectory;
    }

    private function runFixer(string $configName, string $path): void
    {
        $projectRoot = dirname(__DIR__, 2);
        (new Process([
            $projectRoot . '/vendor/bin/php-cs-fixer',
            'fix',
            '--config=' . $projectRoot . '/config/' . $configName,
            '--using-cache=no',
            $path,
        ]))->mustRun();
    }
}
