# hidden-hint/php-inspections

Php Inspections (EA Extended)-style inspections for VS Code, built from three tools that each own one job:

| Job | Tool | Shipped as |
|---|---|---|
| Detection (editor diagnostics) | PHPStan | `extension.neon` (custom rules + strict-rules + ergebnis) |
| Replacements | Rector | `config/rector.php` |
| Formatting (Format Document) | PHP CS Fixer | `config/php-cs-fixer.php` |
| Style refactorings (manual) | PHP CS Fixer | `config/php-cs-fixer-refactor.php` |

See [docs/coverage.md](docs/coverage.md) for which EA inspection is covered by which tool.

`config/php-cs-fixer.php` only changes layout (`@PER-CS`, blank lines) and runs only when you format a document. It
deviates from `@PER-CS` by allowing a file docblock directly after `<?php`.
Everything that rewrites code (`==` to `===`, `declare(strict_types=1)`, `final`, `static` closures, strict
`in_array`, imports, removing `else`) lives in `config/php-cs-fixer-refactor.php` and only runs when invoked by hand.

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
composer global require hidden-hint/php-inspections:@dev

mkdir -p ~/.config/php-inspections
sed "s#{composer-home}#$(composer global config home)#g" \
    ~/Work/hidden-hint/php-inspections/docs/global/phpstan.neon > ~/.config/php-inspections/phpstan.neon
cp ~/Work/hidden-hint/php-inspections/docs/global/project.php ~/.config/php-inspections/project.php
```

The global config includes strict-rules, ergebnis and this package's `extension.neon` by absolute path.
`phpstan/extension-installer` is deliberately not installed globally: Composer runs global plugins during every
`composer install` in any project, and the installer would overwrite the global extension list with that project's.

The analysed project needs its own `vendor/` (run `composer install` there) so PHPStan can discover its classes.
PHPStan reads the project's `require.php` range, so PHP 8.3-only rules stay silent for projects that allow older PHP.

### Per-project settings

`project.php` is included by the global config and returns the parameters that depend on the analysed project.
PHPStan runs from the project root (VS Code uses the workspace folder), so `getcwd()` identifies the project. By
default it analyses the whole project except `vendor/`.

To handle a kind of project differently, detect it by a file it always has and return its own parameters. For a
Magento 1 shop whose own modules live in `app/code/YourVendor`:

```php
<?php

declare(strict_types=1);

$root = getcwd();

return is_file($root . '/app/Mage.php')
    ? ['parameters' => [
        'paths' => [$root . '/app/code/YourVendor'],
        'scanFiles' => [$root . '/app/Mage.php'],
        'scanDirectories' => [$root . '/app/code', $root . '/lib'],
        'ignoreErrors' => [['identifier' => 'ergebnis.noExtends']],
    ]]
    : ['parameters' => [
        'paths' => [$root],
        'excludePaths' => ['analyseAndScan' => [$root . '/vendor (?)']],
    ]];
```

- Keep `paths` out of `phpstan.neon`: PHPStan merges list parameters from included files, so a `paths` there would
  be added to the project's instead of replaced.
- `scanDirectories` lets PHPStan find classes outside `paths` without reporting on them. Prefer it over
  `bootstrapFiles` with the project's own autoloader: Magento 1's `app/Mage.php` registers an autoloader that
  declares classes PHPStan has already loaded and crashes the analysis.
- `app/Mage.php` sits outside `app/code` and `lib`, so it needs its own `scanFiles` entry. If anything in `paths`
  declares a stub `Mage` class (test bootstraps often do), exclude it: PHPStan would otherwise resolve `Mage` to the
  stub and report `Mage::getModel()` and friends as undefined.
- Magento 1 requires inheritance, so `ergebnis.noExtends` would fire on every class.
- Code whose dependencies are not installed in `vendor/` cannot be analysed: leave it out of `paths`.

## Fixing a project

Rector first, then PHP CS Fixer, from the project root (Rector reads the PHP version from its `composer.json`):

```sh
COMPOSER_HOME="$(composer global config home)"
rector process <path> --config "$COMPOSER_HOME/vendor/hidden-hint/php-inspections/config/rector.php"
php-cs-fixer fix <path> --config "$COMPOSER_HOME/vendor/hidden-hint/php-inspections/config/php-cs-fixer-refactor.php"
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
    "php-cs-fixer.allowRisky": false,
    "php-cs-fixer.exclude": ["**/*.phtml"],
    "php-cs-fixer.onsave": false
}
```

`php-cs-fixer.exclude` keeps the fixer away from templates: PHP CS Fixer indents PHP blocks without seeing the
surrounding HTML, so it breaks the indentation of mixed HTML/PHP files.

With `php-cs-fixer.onsave` off, files are only formatted on command (**Format Document**, `⇧⌥F`). Turning it on would
also format on every auto-save.

## Development

```sh
composer install
vendor/bin/phpunit
vendor/bin/phpstan analyse
vendor/bin/php-cs-fixer check
bin/smoke-test.sh
```

`bin/smoke-test.sh` installs `tests/smoke/sample-project`, runs the globally installed PHPStan against it and checks that
one custom rule from each group is reported.
