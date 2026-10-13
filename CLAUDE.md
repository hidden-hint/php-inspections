# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```sh
composer install
vendor/bin/phpunit                                   # all tests
vendor/bin/phpunit --filter MemoizedPropertyRuleTest # single test class (or method name)
vendor/bin/phpstan analyse                           # self-analysis at level max, with this package's own rules
vendor/bin/php-cs-fixer check                        # uses config/php-cs-fixer-refactor.php on config/, src/, tests/
vendor/bin/php-cs-fixer fix
bin/smoke-test.sh                                    # needs the global install from README.md, plus jq
```

## What this is

A PHPStan extension package (`type: phpstan-extension`) that recreates Php Inspections (EA Extended) for VS Code by
splitting the work across three tools:

- **PHPStan** detects. `extension.neon` holds the custom rules, the abbreviation denylist and ergebnis overrides. It is
  loaded through `extra.phpstan.includes` locally and by absolute path from the global config
  (`docs/global/phpstan.neon` + `docs/global/project.php`) for real projects.
- **Rector** replaces (`config/rector.php`): PHP sets, prepared sets and the custom rectors in `src/Rector`.
- **PHP CS Fixer** has two configs. `config/php-cs-fixer.php` is layout only (Format Document) and must never rewrite
  code. `config/php-cs-fixer-refactor.php` extends it with the risky rewrites and is only run by hand. Anything that
  changes semantics belongs in the refactor config.

`docs/coverage.md` maps every EA inspection to the tool and rule id covering it; update it when adding a rule. The
custom rule table in `README.md` lists every `phpInspections.*` identifier; keep it in sync too.

## PHPStan rules (`src/PHPStan/Rules`)

- Each rule reports with a `phpInspections.*` identifier. Messages and identifiers are typed class constants.
- Shared logic lives in small collaborator classes (e.g. `InheritedSignatureDetector`, `EmptinessComparisonMatcher`,
  `YodaComparisonDetector`, `BoundedNodeFinder`, `AbbreviationDenylist`) injected via constructor, not base classes.
- Registration in `extension.neon`: rules without dependencies go under `rules:`; rules with dependencies are
  `services:` entries tagged `phpstan.rules.rule`, with their collaborators registered as services.
  `PhpParser\NodeFinder` is registered as the non-autowired `phpInspections.nodeFinder` service.
- New configurable parameters need a matching `parametersSchema` entry.
- PHP 8.3-only rules check PHPStan's `phpVersion` range (derived from the analysed project's `composer.json`), so they
  stay silent for projects that allow older PHP. Tests override the range with a neon file from
  `tests/PHPStan/Rules/config` via `getAdditionalConfigFiles()`.

## Tests

- Rule tests extend `PHPStan\Testing\RuleTestCase` and analyse a file in `tests/PHPStan/Rules/data/`, asserting
  `[message, line]` pairs. Data directories are `classmap`-autoloaded, so run `composer dump-autoload` after adding a
  data file that declares classes.
- Rector tests extend `AbstractRectorTestCase` with `.php.inc` fixtures (input / `-----` / expected; `skip_*` fixtures
  have no expected part) and a `config/configured_rule.php`.
- `tests/CodingStandard/RulesetTest` and `tests/Rector/SharedConfig/SharedConfigTest` run the real binaries on temp
  copies of fixtures and compare against expected files; `tests/PHPStan/ExistingPacksTest` checks that strict-rules
  and ergebnis are active and configured as intended.
- Fixture/data directories are excluded from PHPStan and PHP CS Fixer self-analysis (`phpstan.neon`,
  `.php-cs-fixer.dist.php`); keep new fixture locations under those patterns.

## Conventions

The package enforces its own style on itself: level max with strict-rules and ergebnis, `final` classes, typed
constants, no default parameter values or boolean flags, Yoda `===`, no abbreviations, higher-order functions over
loops. `extends` is only allowed for the classes listed under `ergebnis.noExtends.classesAllowedToBeExtended` in
`phpstan.neon`.
