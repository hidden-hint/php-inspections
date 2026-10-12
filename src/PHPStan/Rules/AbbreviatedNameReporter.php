<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\PHPStan\Rules;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\VarLikeIdentifier;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\RuleErrorBuilder;

use function array_filter;
use function array_map;
use function array_values;
use function is_string;
use function sprintf;

final class AbbreviatedNameReporter
{
    private const string MESSAGE = 'Name $%s is an abbreviation; use a full descriptive word.';
    private const string IDENTIFIER = 'phpInspections.abbreviatedName';

    public function __construct(private readonly AbbreviationDenylist $abbreviationDenylist) {}

    /**
     * @return list<IdentifierRuleError>
     */
    public function report(Variable|VarLikeIdentifier ...$names): array
    {
        return array_map(
            $this->buildError(...),
            array_values(array_filter($names, $this->isAbbreviation(...))),
        );
    }

    private function isAbbreviation(Variable|VarLikeIdentifier $name): bool
    {
        return $this->abbreviationDenylist->contains($this->nameOf($name));
    }

    private function nameOf(Variable|VarLikeIdentifier $name): string
    {
        return $name instanceof VarLikeIdentifier ? $name->toString() : (is_string($name->name) ? $name->name : '');
    }

    private function buildError(Variable|VarLikeIdentifier $name): IdentifierRuleError
    {
        return RuleErrorBuilder::message(sprintf(self::MESSAGE, $this->nameOf($name)))
            ->identifier(self::IDENTIFIER)
            ->line($name->getStartLine())
            ->build();
    }
}
