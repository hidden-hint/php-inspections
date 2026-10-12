<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\PHPStan\Rules;

use function array_values;
use function in_array;

final readonly class AbbreviationDenylist
{
    /**
     * @var list<string>
     */
    private array $abbreviations;

    public function __construct(string ...$abbreviations)
    {
        $this->abbreviations = array_values($abbreviations);
    }

    public function contains(string $name): bool
    {
        return in_array($name, $this->abbreviations, true);
    }
}
