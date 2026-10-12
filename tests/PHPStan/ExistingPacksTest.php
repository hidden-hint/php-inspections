<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\Tests\PHPStan;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

use function array_column;
use function array_map;
use function array_merge;
use function array_values;
use function dirname;
use function is_array;
use function json_decode;

use const JSON_THROW_ON_ERROR;

final class ExistingPacksTest extends TestCase
{
    public function testExistingPacksReportEmptyAndLooseComparison(): void
    {
        $identifiers = $this->analyseAndCollectIdentifiers(__DIR__ . '/data/existing-packs.php');

        self::assertContains('empty.notAllowed', $identifiers);
        self::assertContains('equal.notAllowed', $identifiers);
        self::assertNotContains('ergebnis.noIsset', $identifiers);
    }

    /**
     * @return list<mixed>
     */
    private function analyseAndCollectIdentifiers(string $path): array
    {
        $projectRoot = dirname(__DIR__, 2);
        $process = new Process(
            [
                $projectRoot . '/vendor/bin/phpstan',
                'analyse',
                '--error-format=json',
                '--no-progress',
                '--level=max',
                '--configuration=' . $projectRoot . '/extension.neon',
                $path,
            ],
            $projectRoot,
        );
        $process->run();

        return $this->collectIdentifiers(json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * @return list<mixed>
     */
    private function collectIdentifiers(mixed $report): array
    {
        self::assertIsArray($report);
        self::assertIsArray($report['files']);

        return array_merge([], ...array_map(
            static fn(mixed $file): array => is_array($file) && is_array($file['messages'])
                ? array_column($file['messages'], 'identifier')
                : [],
            array_values($report['files']),
        ));
    }
}
