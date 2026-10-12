# hidden-hint/php-inspections

Php Inspections (EA Extended)-style inspections for VS Code, built from three tools that each own one job:

| Job | Tool | Shipped as |
|---|---|---|
| Detection (editor diagnostics) | PHPStan | `extension.neon` (custom rules + strict-rules + ergebnis) |
| Replacements | Rector | `config/rector.php` |
| Formatting and style | PHP CS Fixer | `config/php-cs-fixer.php` |

See [docs/coverage.md](docs/coverage.md) for which EA inspection is covered by which tool.

## Custom PHPStan rules

| Identifier | Reports |
|---|---|
| `phpInspections.untypedClassConstant` | Class constants without a type (PHP 8.3+) |
| `phpInspections.memoizedPropertyUntyped` / `Nullable` / `Default` | `memoized*` properties that are untyped, nullable or initialised |
| `phpInspections.booleanFlagParameter` | `bool` parameters of functions and methods (overrides and promoted properties excluded) |
| `phpInspections.parameterDefaultValue` | Parameters with default values (overrides and closures excluded) |
| `phpInspections.abbreviatedName` | Variables, parameters and properties named after the denylist |
| `phpInspections.yodaIdentical` | `$x === CONSTANT` instead of `CONSTANT === $x` |
| `phpInspections.countEmptinessCheck` | `count($array) === 0` and equivalents on provable arrays |
| `phpInspections.strlenEmptinessCheck` | `strlen($string) === 0` and equivalents on non-nullable strings |
| `phpInspections.callInForCondition` | `count()` / `strlen()` / `mb_strlen()` in a `for` condition |
| `phpInspections.arrayMergeInLoop` | `$result = array_merge($result, …)` inside a loop |

The abbreviation denylist is configurable:

```neon
parameters:
    phpInspections:
        abbreviations: [e, tmp, cfg]
```

## Global install

```sh
composer global config repositories.php-inspections path ~/Work/hidden-hint/php-inspections
composer global config allow-plugins.phpstan/extension-installer true
composer global require hidden-hint/php-inspections:@dev

mkdir -p ~/.config/php-inspections
cp ~/Work/hidden-hint/php-inspections/docs/global/phpstan.neon ~/.config/php-inspections/phpstan.neon
```

Check that PHPStan picked up the extensions:

```sh
~/.composer/vendor/bin/phpstan diagnose -c ~/.config/php-inspections/phpstan.neon
```

The `Extension installer` section must list `hidden-hint/php-inspections`, `ergebnis/phpstan-rules` and
`phpstan/phpstan-strict-rules`. If it says `No extensions installed`, regenerate the list with
`composer global update phpstan/extension-installer`.

The analysed project needs its own `vendor/` (run `composer install` there) so PHPStan can discover its classes.

## Fixing a project

Rector first, then PHP CS Fixer:

```sh
COMPOSER_HOME="$(composer global config home)"
rector process <path> --config "$COMPOSER_HOME/vendor/hidden-hint/php-inspections/config/rector.php"
php-cs-fixer fix <path> --config "$COMPOSER_HOME/vendor/hidden-hint/php-inspections/config/php-cs-fixer.php"
```

## VS Code

Install the extensions:

```sh
code --install-extension SanderRonde.phpstan-vscode
code --install-extension junstyle.php-cs-fixer
```

User `settings.json` (replace `/Users/you` with your home directory):

```json
{
    "phpstan.binPath": "/Users/you/.composer/vendor/bin/phpstan",
    "phpstan.configFile": "/Users/you/.config/php-inspections/phpstan.neon",
    "php-cs-fixer.executablePath": "/Users/you/.composer/vendor/bin/php-cs-fixer",
    "php-cs-fixer.config": "/Users/you/.composer/vendor/hidden-hint/php-inspections/config/php-cs-fixer.php",
    "php-cs-fixer.allowRisky": true,
    "php-cs-fixer.onsave": true
}
```

`php-cs-fixer.onsave` also fires on auto-save, so with `files.autoSave: afterDelay` the fixer runs after every pause in
typing.

## Development

```sh
composer install
vendor/bin/phpunit
vendor/bin/phpstan analyse
vendor/bin/php-cs-fixer check
bin/smoke-test.sh
```

`bin/smoke-test.sh` runs the globally installed PHPStan against `tests/smoke/sample-project` and checks that one custom
rule from each group is reported.
