# PHP Inspections (PHPStan + Rector + PHP CS Fixer) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Bring Php Inspections (EA Extended)-style inspections into VS Code as editor diagnostics, with automated replacements and formatting, installed once system-wide.

**Architecture:** One Composer package `hidden-hint/php-inspections` ships three things: a PHPStan extension (detection → VS Code highlighting via the PHPStan extension), Rector rules plus a Rector config (replacements), and a shared PHP CS Fixer ruleset (formatting/style). Existing rule packs cover what they can; custom rules fill only the gaps. Installed with `composer global require`; VS Code points at the global binaries and configs.

**Tech Stack:** PHP 8.3, PHPStan ^2.1, Rector ^2.0, PHP CS Fixer ^3.64, PHPUnit ^11, phpstan/phpstan-strict-rules, ergebnis/phpstan-rules, phpstan/extension-installer.

**Spec:** No separate spec — the requirements are the 2026-10-12 conversation and `~/.claude/CLAUDE.md` (Coding Preferences → PHP).

## Global Constraints

- Package name `hidden-hint/php-inspections`, root namespace `HiddenHint\PhpInspections\`, PSR-4 from `src/`, tests `HiddenHint\PhpInspections\Tests\` from `tests/`.
- PHP `>=8.3` for the package itself; rules must not report things the analysed project's PHP version cannot express (use `PHPStan\Php\PhpVersion`).
- Package code follows `~/.claude/CLAUDE.md` PHP rules: `declare(strict_types=1)`, `final` classes, no comments, typed constants, static closures, no `empty()`, Yoda for `===`, blank line before `return`, full names, imports instead of FQCNs, trailing newline.
- Inheritance only where the framework mandates it (`RuleTestCase`, `AbstractRector`, `AbstractRectorTestCase`); those classes are still `final`.
- Every PHPStan error carries an identifier `phpInspections.<camelCaseName>`.
- Detection lives in PHPStan, replacements in Rector, formatting in PHP CS Fixer. A behaviour belongs to exactly one of them.
- Run order when fixing: Rector, then PHP CS Fixer.

## Review Focus

1. `count()` on a `Countable` object (e.g. a Doctrine collection) — must be neither reported nor rewritten to `[] ===`; only provably-array arguments qualify. (Task 6, Task 8)
2. `strlen()` on `?string`, `int` or `mixed` — `'' === $x` changes semantics; only provably non-nullable strings qualify. (Task 6, Task 8)
3. `bool` parameters / default values in methods that override a parent or implement an interface — signature is not the author's to change; must not be reported. Promoted `bool` constructor properties are value-object state, not flags; must not be reported. (Task 4)
4. Short but legitimate names (`$id`, `$url`, `$sku`) — must not be reported; only names on the configured denylist are. (Task 5)
5. `$result = array_merge($other, $chunk)` in a loop where `$result` is not an argument — not an accumulation; must not be reported. (Task 7)

---

### Task 1: Package scaffold and shared PHP CS Fixer ruleset

**Files:**
- Create: `composer.json`, `phpunit.xml.dist`, `phpstan.neon` (self-analysis, level `max`), `extension.neon` (empty `rules: []` for now), `.php-cs-fixer.dist.php`, `config/php-cs-fixer.php`, `.gitignore` (`/vendor/`, `.phpunit.cache`, `.php-cs-fixer.cache`)
- Test: `tests/CodingStandard/fixtures/unformatted.php`, `tests/CodingStandard/fixtures/formatted.php`, `tests/CodingStandard/RulesetTest.php`

**Interfaces:**
- Produces: `config/php-cs-fixer.php` returns `PhpCsFixer\Config` (risky allowed) with exactly: `@PER-CS`, `declare_strict_types`, `final_class`, `static_lambda`, `strict_comparison`, `strict_param`, `blank_line_before_statement` (`statements: ['return']`), `global_namespace_import` (`import_classes: true, import_constants: true, import_functions: true`), `no_alias_functions`, `is_null`, `modernize_strpos`, `ternary_to_null_coalescing`, `no_useless_else`, `single_blank_line_at_eof`, `yoda_style` set to `false` for all three options (Yoda for `===` only is enforced by Task 5's PHPStan rule, because `yoda_style` cannot separate `===` from `!==`).
- Produces: `composer.json` `type: phpstan-extension`, `extra.phpstan.includes: ["extension.neon"]`.

- [ ] **Step 1:** `git init`, write `composer.json` (require: `php`, `phpstan/phpstan`, `phpstan/phpstan-strict-rules`, `ergebnis/phpstan-rules`, `phpstan/extension-installer`, `rector/rector`, `friendsofphp/php-cs-fixer`; require-dev: `phpunit/phpunit`), run `composer install`. Expected: exit 0.
- [ ] **Step 2: Write the failing test** `RulesetTest::testFormatsFixtureToExpectedOutput` — copies `unformatted.php` to a temp dir, runs `vendor/bin/php-cs-fixer fix --config=config/php-cs-fixer.php <tmp>` via `Symfony\Component\Process\Process`, asserts `assertFileEquals(formatted.php, <tmp>/unformatted.php)`. Fixture covers: missing strict_types, non-final class, non-static closure, `==`, `in_array` without strict, `sizeof`, missing blank line before return, FQCN `\Throwable`.
- [ ] **Step 3:** Run `vendor/bin/phpunit --filter RulesetTest`. Expected: FAIL (config missing).
- [ ] **Step 4:** Write `config/php-cs-fixer.php`, `.php-cs-fixer.dist.php` (requires the shared config, finder on `src/` and `tests/`, excluding `tests/**/data` and `tests/**/Fixture`), `phpstan.neon`, `extension.neon`, `phpunit.xml.dist`.
- [ ] **Step 5:** Run `vendor/bin/phpunit && vendor/bin/phpstan analyse && vendor/bin/php-cs-fixer check`. Expected: all green.
- [ ] **Step 6: Commit** `chore: scaffold package with shared PHP CS Fixer ruleset`

### Task 2: Enable existing PHPStan rule packs and document coverage

**Files:**
- Modify: `extension.neon`
- Create: `docs/coverage.md`
- Test: `tests/PHPStan/ExistingPacksTest.php`, `tests/PHPStan/data/existing-packs.php`

**Interfaces:**
- Produces: `extension.neon` includes strict-rules and ergebnis rules (via extension-installer), with ergebnis `Expressions\NoIssetRule` disabled (conflicts with memoized `isset()` pattern) and any ergebnis rule that duplicates Tasks 3–7 disabled. Verify parameter names against the installed ergebnis README.
- Produces: `docs/coverage.md` — table with columns `EA inspection | Covered by | Rule/fixer id`, one row per EA inspection group from the kalessil/phpinspectionsea docs, values one of `PHPStan core`, `strict-rules`, `ergebnis`, `PHP CS Fixer`, `Rector set`, `custom (Task N)`, `not planned`.

- [ ] **Step 1: Write the failing test** `ExistingPacksTest::testExistingPacksReportEmptyAndLooseComparison` — runs `vendor/bin/phpstan analyse --error-format=json --configuration=extension.neon` (plus `level: max`) on `data/existing-packs.php`, asserts the JSON contains identifiers `empty.notAllowed` and `equal.notAllowed` and does **not** contain an `isset`-related ergebnis identifier for `isset($this->memoizedItems)`.
- [ ] **Step 2:** Run `vendor/bin/phpunit --filter ExistingPacksTest`. Expected: FAIL.
- [ ] **Step 3:** Configure `extension.neon`; write `docs/coverage.md`.
- [ ] **Step 4:** Run `vendor/bin/phpunit --filter ExistingPacksTest`. Expected: PASS.
- [ ] **Step 5: Commit** `feat: enable strict-rules and ergebnis packs, add EA coverage matrix`

### Task 3: TypedClassConstantRule and MemoizedPropertyRule

**Files:**
- Create: `src/PHPStan/Rules/TypedClassConstantRule.php`, `src/PHPStan/Rules/MemoizedPropertyRule.php`
- Modify: `extension.neon` (register both)
- Test: `tests/PHPStan/Rules/TypedClassConstantRuleTest.php`, `tests/PHPStan/Rules/MemoizedPropertyRuleTest.php`, `tests/PHPStan/Rules/data/typed-class-constant.php`, `tests/PHPStan/Rules/data/memoized-property.php`

**Interfaces:**
- Produces: `final class TypedClassConstantRule implements Rule<ClassConst>`, constructor `(PhpVersion $phpVersion)`. Error `Class constant %s must declare a type.`, identifier `phpInspections.untypedClassConstant`. Silent when `$phpVersion->getVersionId() < 80300`.
- Produces: `final class MemoizedPropertyRule implements Rule<Property>`. Applies to properties named `memoized*`. Errors: `Memoized property $%s must declare a type.` (`phpInspections.memoizedPropertyUntyped`), `Memoized property $%s must not be nullable.` (`phpInspections.memoizedPropertyNullable`), `Memoized property $%s must not have a default value.` (`phpInspections.memoizedPropertyDefault`).

- [ ] **Step 1: Write the failing tests.** Shape for every rule test in this plan:

```php
public function testReportsUntypedConstants(): void
{
    $this->analyse([__DIR__ . '/data/typed-class-constant.php'], [
        ['Class constant LIMIT must declare a type.', 9],
    ]);
}
```

  `TypedClassConstantRuleTest`: `testReportsUntypedConstants` (rule built with `new PhpVersion(80300)`; fixture has one typed and one untyped constant, enum constant included), `testSilentBelowPhp83` (`new PhpVersion(80200)`, expects `[]`). `MemoizedPropertyRuleTest::testReportsInvalidMemoizedProperties` — fixture: `private ?array $memoizedItems = null;` (2 errors: nullable, default), `private $memoizedTotal;` (untyped), `private array $memoizedValid;` (none), `private ?array $items = null;` (none, not memoized).
- [ ] **Step 2:** Run `vendor/bin/phpunit --filter 'TypedClassConstantRuleTest|MemoizedPropertyRuleTest'`. Expected: FAIL (class not found).
- [ ] **Step 3:** Implement both rules; register in `extension.neon` (`TypedClassConstantRule` as a service tagged `phpstan.rules.rule` so `PhpVersion` is autowired).
- [ ] **Step 4:** Run the same filter. Expected: PASS. Then `vendor/bin/phpstan analyse && vendor/bin/php-cs-fixer check`.
- [ ] **Step 5: Commit** `feat: add typed class constant and memoized property rules`

### Task 4: BooleanFlagParameterRule and ParameterDefaultValueRule

**Files:**
- Create: `src/PHPStan/Rules/BooleanFlagParameterRule.php`, `src/PHPStan/Rules/ParameterDefaultValueRule.php`, `src/PHPStan/Rules/InheritedSignatureDetector.php`
- Modify: `extension.neon`
- Test: `tests/PHPStan/Rules/BooleanFlagParameterRuleTest.php`, `tests/PHPStan/Rules/ParameterDefaultValueRuleTest.php`, `tests/PHPStan/Rules/data/boolean-flag-parameter.php`, `tests/PHPStan/Rules/data/parameter-default-value.php`

**Interfaces:**
- Produces: `final class InheritedSignatureDetector` with `isInherited(ClassMethod $method, Scope $scope): bool` — true when any parent class or implemented interface declares the method name.
- Produces: `BooleanFlagParameterRule implements Rule<FunctionLike>` — reports `bool`, `true`, `false` typed parameters of `Function_` and `ClassMethod`. Skips closures/arrow functions, promoted constructor properties, inherited signatures. Error `Parameter $%s is a boolean flag; split the method into two named methods.`, identifier `phpInspections.booleanFlagParameter`.
- Produces: `ParameterDefaultValueRule implements Rule<FunctionLike>` — reports any parameter with a default on `Function_`, `ClassMethod` (including promoted), skips closures/arrow functions and inherited signatures. Error `Parameter $%s must not have a default value.`, identifier `phpInspections.parameterDefaultValue`.

- [ ] **Step 1: Write the failing tests.** `BooleanFlagParameterRuleTest::testReportsBooleanFlags` fixture cases: function with `bool $force` (error), method with `?bool $strict` (error), `__construct(private bool $enabled)` (none), method implementing an interface method with `bool` param (none), `static fn (bool $value) => $value` (none). `ParameterDefaultValueRuleTest::testReportsDefaults`: method `int $limit = 10` (error), promoted `private int $limit = 10` (error), overriding method with default (none), closure with default (none).
- [ ] **Step 2:** Run `vendor/bin/phpunit --filter 'BooleanFlagParameterRuleTest|ParameterDefaultValueRuleTest'`. Expected: FAIL.
- [ ] **Step 3:** Implement detector and both rules; register.
- [ ] **Step 4:** Run the filter. Expected: PASS. Then self-analysis and CS check.
- [ ] **Step 5: Commit** `feat: add boolean flag and parameter default value rules`

### Task 5: AbbreviatedNameRule and YodaIdenticalComparisonRule

**Files:**
- Create: `src/PHPStan/Rules/AbbreviatedNameRule.php`, `src/PHPStan/Rules/YodaIdenticalComparisonRule.php`
- Modify: `extension.neon` (register; add parameter `phpInspections.abbreviations` with `parametersSchema` `listOf(string())`)
- Test: `tests/PHPStan/Rules/AbbreviatedNameRuleTest.php`, `tests/PHPStan/Rules/YodaIdenticalComparisonRuleTest.php`, data fixtures

**Interfaces:**
- Produces: `AbbreviatedNameRule implements Rule<Node>` filtering `Param`, `Expr\Variable` in assignment/foreach/catch position, `PropertyItem`. Constructor `(string ...$abbreviations)`; extension.neon passes `%phpInspections.abbreviations%` with default list: `e, ex, f, i, j, k, v, arr, str, obj, cnt, idx, tmp, val, res, req, resp, cb, fn, ctx, cfg, conf, msg, err, num, len, el, elem, buf, ptr`. Exact match on the whole name only. Error `Name $%s is an abbreviation; use a full descriptive word.`, identifier `phpInspections.abbreviatedName`. One error per declaration, not per usage.
- Produces: `YodaIdenticalComparisonRule implements Rule<BinaryOp\Identical>`. Constant-like = `Scalar`, `ConstFetch`, `ClassConstFetch`, empty `Array_`, `UnaryMinus` of `Scalar`. Reports when right side is constant-like and left is not. Error `Use Yoda notation: put the constant operand on the left side of ===.`, identifier `phpInspections.yodaIdentical`. `NotIdentical` is never reported.

- [ ] **Step 1: Write the failing tests.** Abbreviation fixture: `catch (Throwable $e)` (error), `foreach ($items as $k => $v)` (2 errors), `$id`, `$url`, `$sku` (none), `$exception` (none), parameter `$cfg` (error). Test `testRespectsCustomList` builds the rule with `new AbbreviatedNameRule('sku')` and expects only `$sku` reported. Yoda fixture: `$x === null` (error), `null === $x` (none), `$x === self::FOO` (error), `$x === []` (error), `$x !== null` (none), `1 === 2` (none), `$a === $b` (none).
- [ ] **Step 2:** Run `vendor/bin/phpunit --filter 'AbbreviatedNameRuleTest|YodaIdenticalComparisonRuleTest'`. Expected: FAIL.
- [ ] **Step 3:** Implement and register.
- [ ] **Step 4:** Run the filter. Expected: PASS. Then self-analysis and CS check.
- [ ] **Step 5: Commit** `feat: add abbreviated name and Yoda identical comparison rules`

### Task 6: CountEmptinessCheckRule and StrlenEmptinessCheckRule

**Files:**
- Create: `src/PHPStan/Rules/EmptinessComparisonMatcher.php`, `src/PHPStan/Rules/CountEmptinessCheckRule.php`, `src/PHPStan/Rules/StrlenEmptinessCheckRule.php`
- Modify: `extension.neon`
- Test: rule tests + `tests/PHPStan/Rules/data/count-emptiness.php`, `tests/PHPStan/Rules/data/strlen-emptiness.php`

**Interfaces:**
- Produces: `final class EmptinessComparisonMatcher` with `match(BinaryOp $comparison, string ...$functionNames): ?EmptinessComparison` and `final readonly class EmptinessComparison` (`FuncCall $call`, `Expr $subject`, `bool $checksEmpty`) — recognised forms (either operand order): `=== 0`, `== 0`, `!== 0`, `!= 0`, `> 0`, `< 1`, `>= 1`, `<= 0`. Task 8 reuses both classes.
- Produces: `CountEmptinessCheckRule implements Rule<BinaryOp>` for `count`; reports only when `$scope->getType($subject)->isArray()->yes()`. Messages `Use [] === %s instead of count() to check emptiness.` / `Use [] !== %s ...`, identifier `phpInspections.countEmptinessCheck`.
- Produces: `StrlenEmptinessCheckRule implements Rule<BinaryOp>` for `strlen`, `mb_strlen`; reports only when `isString()->yes()` and the type is not nullable. Messages `Use '' === %s instead of strlen() to check emptiness.` / `'' !==`, identifier `phpInspections.strlenEmptinessCheck`.
- `%s` is the subject printed with `PhpParser\PrettyPrinter\Standard::prettyPrintExpr()`.

- [ ] **Step 1: Write the failing tests.** Count fixture: `array $items` with `count($items) === 0`, `0 < count($items)`, `count($items) >= 1` (3 errors with correct `===`/`!==` wording); `Countable $collection` with `count($collection) === 0` (none); `count($items) === 2` (none). Strlen fixture: `string $name` with `strlen($name) === 0` and `mb_strlen($name) > 0` (2 errors); `?string $maybe` and `int $number` (none).
- [ ] **Step 2:** Run `vendor/bin/phpunit --filter 'CountEmptinessCheckRuleTest|StrlenEmptinessCheckRuleTest'`. Expected: FAIL.
- [ ] **Step 3:** Implement matcher, value object, both rules; register.
- [ ] **Step 4:** Run the filter. Expected: PASS. Then self-analysis and CS check.
- [ ] **Step 5: Commit** `feat: add count and strlen emptiness check rules`

### Task 7: CountInForConditionRule and ArrayMergeInLoopRule

**Files:**
- Create: `src/PHPStan/Rules/CountInForConditionRule.php`, `src/PHPStan/Rules/ArrayMergeInLoopRule.php`
- Modify: `extension.neon`
- Test: rule tests + `tests/PHPStan/Rules/data/count-in-for-condition.php`, `tests/PHPStan/Rules/data/array-merge-in-loop.php`

**Interfaces:**
- Produces: `CountInForConditionRule implements Rule<Stmt\For_>` — reports each `count`/`strlen`/`mb_strlen` `FuncCall` found in `$node->cond`. Error `Move %s() out of the for loop condition; it is re-evaluated on every iteration.`, identifier `phpInspections.callInForCondition`.
- Produces: `ArrayMergeInLoopRule implements Rule<Stmt>` filtering `Foreach_`, `For_`, `While_`, `Do_`; searches the loop body with `PhpParser\NodeFinder` (not descending into `Closure`, `ArrowFunction`, `Function_`, `Class_`) for `Assign(Variable $name, FuncCall array_merge)` where an argument is `Variable $name`. Error `Accumulating with array_merge() in a loop is quadratic; collect chunks and call array_merge(...$chunks) once.`, identifier `phpInspections.arrayMergeInLoop`. Nested loops report once (innermost).

- [ ] **Step 1: Write the failing tests.** For fixture: `for ($index = 0; $index < count($items); $index++)` (error), count hoisted into init (none). Merge fixture: `$result = array_merge($result, $chunk)` inside `foreach` (error), `$merged = array_merge($defaults, $chunk)` in loop (none), accumulation inside a closure defined in a loop (none), nested `foreach` accumulation (exactly one error).
- [ ] **Step 2:** Run `vendor/bin/phpunit --filter 'CountInForConditionRuleTest|ArrayMergeInLoopRuleTest'`. Expected: FAIL.
- [ ] **Step 3:** Implement and register.
- [ ] **Step 4:** Run the filter. Expected: PASS. Then self-analysis and CS check.
- [ ] **Step 5: Commit** `feat: add loop performance rules`

### Task 8: Rector rules and shared Rector config

**Files:**
- Create: `src/Rector/CountEmptinessCheckToArrayComparisonRector.php`, `src/Rector/StrlenEmptinessCheckToStringComparisonRector.php`, `config/rector.php`
- Test: `tests/Rector/CountEmptinessCheckToArrayComparisonRector/CountEmptinessCheckToArrayComparisonRectorTest.php` + `Fixture/*.php.inc` + `config/configured_rule.php`; same layout for the strlen rector

**Interfaces:**
- Consumes: `EmptinessComparisonMatcher::match()`, `EmptinessComparison` (Task 6).
- Produces: both rectors `final class ... extends AbstractRector`, `getNodeTypes(): [BinaryOp::class]`, `refactor(Node $node): ?Node`. Output is always Yoda: `[] === $subject` / `[] !== $subject`, `'' === $subject` / `'' !== $subject`. Same type guards as Task 6 via `$this->getType($subject)`; return `null` when the guard fails.
- Produces: `config/rector.php` returning `RectorConfig::configure()->withPhpSets()->withPreparedSets(deadCode: true, codeQuality: true, typeDeclarations: true)->withImportNames()->withRules([both rectors])` — no paths (passed on the CLI).

- [ ] **Step 1: Write the failing tests.** Each test uses `AbstractRectorTestCase` with `#[DataProvider]` over `Fixture/`. Count fixtures: `count_equals_zero.php.inc` (`count($items) === 0` → `[] === $items`), `zero_less_than_count.php.inc` (`0 < count($items)` → `[] !== $items`), `skip_countable.php.inc` (unchanged). Strlen fixtures: `strlen_equals_zero.php.inc`, `mb_strlen_greater_than_zero.php.inc`, `skip_nullable_string.php.inc`.
- [ ] **Step 2:** Run `vendor/bin/phpunit tests/Rector`. Expected: FAIL.
- [ ] **Step 3:** Implement both rectors and `config/rector.php`.
- [ ] **Step 4:** Run `vendor/bin/phpunit tests/Rector`. Expected: PASS. Then full suite, self-analysis, CS check.
- [ ] **Step 5: Commit** `feat: add emptiness-check rectors and shared Rector config`

### Task 9: Global install and VS Code wiring

**Files:**
- Create: `README.md` (install + VS Code setup), `docs/global/phpstan.neon` (template copied to `~/.config/php-inspections/phpstan.neon`: `level: max`, `paths: [.]`, `excludePaths: [vendor]`), `bin/smoke-test.sh`
- Test: `tests/smoke/sample-project/` (composer.json with `php: ^8.3`, one PHP file violating one rule from each of Tasks 3, 4, 5, 6, 7)

**Interfaces:**
- Consumes: everything above.
- Produces: documented commands — `composer global config repositories.php-inspections path ~/Work/hidden-hint/php-inspections`, `composer global require hidden-hint/php-inspections:@dev`, then `rector process <path> --config ~/.composer/.../config/rector.php` followed by `php-cs-fixer fix <path> --config ~/.composer/.../config/php-cs-fixer.php` (resolve the home with `composer global config home`).
- Produces: VS Code user settings for the PHPStan extension (SanderRonde `phpstan-vscode`: binary path → global `phpstan`, config file → `~/.config/php-inspections/phpstan.neon`) and the PHP CS Fixer extension (executable + config path, format on save). Verify setting keys against each extension's README at implementation time.

- [ ] **Step 1: Write the failing smoke test** `bin/smoke-test.sh`: runs the global `phpstan analyse --error-format=json -c ~/.config/php-inspections/phpstan.neon` inside `tests/smoke/sample-project` and asserts (with `jq`) that identifiers `phpInspections.untypedClassConstant`, `phpInspections.booleanFlagParameter`, `phpInspections.abbreviatedName`, `phpInspections.countEmptinessCheck`, `phpInspections.arrayMergeInLoop` are all present.
- [ ] **Step 2:** Run `bin/smoke-test.sh`. Expected: FAIL (not installed globally).
- [ ] **Step 3:** Perform the global install per README; copy the neon template; run `composer install` in the sample project (PHPStan needs its `vendor/` for class discovery).
- [ ] **Step 4:** Run `bin/smoke-test.sh`. Expected: exit 0. Open the sample project in VS Code and confirm the five diagnostics render after save.
- [ ] **Step 5: Commit** `docs: add global install and VS Code setup`
