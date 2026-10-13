# Php Inspections (EA Extended) coverage

Rows follow the inspection groups documented in
[kalessil/phpinspectionsea `docs/`](https://github.com/kalessil/phpinspectionsea/tree/master/docs),
one row per documented inspection, grouped by the document it appears in. "PHP CS Fixer" means the Format Document
`config/php-cs-fixer.php`; "PHP CS Fixer (refactor)" means the manually run `config/php-cs-fixer-refactor.php`.

| EA inspection | Covered by | Rule/fixer id |
|---|---|---|
| **Architecture** | | |
| Class overrides a field of a parent class | not planned | |
| Long inheritance chain | not planned | |
| Overriding deprecated methods | not planned | |
| Multiple return statements usage | not planned | |
| Transitive dependencies usage | not planned | |
| Development dependencies usage | not planned | |
| Badly organized exception handling | not planned | |
| Callable parameter usage violates definition | PHPStan core | `argument.type` |
| Empty class | not planned | |
| Class implements interfaces multiple times | not planned | |
| **Code style** | | |
| Non-null default value parameters | custom (Task 4) | `phpInspections.parameterDefaultValue` |
| Access modifiers shall be defined | PHP CS Fixer | `modifier_keywords` (via `@PER-CS`) |
| **Confusing constructs** | | |
| Suspicious ternary operator | not planned | |
| Method name matches existing field name | not planned | |
| Nested ternary operator | not planned | |
| Useless return | Rector set | `RemoveDeadReturnRector` (dead code) |
| Referencing objects | ergebnis | `ergebnis.noParameterPassedByReference`, `ergebnis.noAssignByReference` |
| **Control flow** | | |
| Non-optimal if conditions | not planned | |
| PDO API usage | not planned | |
| General Exception is thrown | not planned | |
| If-return-return could be simplified | Rector set | `SimplifyIfReturnBoolRector` (code quality) |
| Loop which does not loop | not planned | |
| Switch-case could be simplified | ergebnis | `ergebnis.noSwitch` |
| Foreach usage possible | not planned | |
| Ternary operator could be simplified | Rector set | `UnnecessaryTernaryExpressionRector` (code quality) |
| Statement could be decoupled from foreach | not planned | |
| `gettype(...)` could be replaced with `is_*(...)` | not planned | |
| `unset(...)` constructs can be merged | not planned | |
| One-time use variables | not planned | |
| `list(...) =` usage possible | not planned | |
| `array_search(...)` could be replaced by `in_array(...)` | not planned | |
| `strtr(...)` could be replaced with `str_replace(...)` | not planned | |
| `substr(...)` could be replaced with `strpos(...)` | Rector set | `StrStartsWithRector` (PHP 8.0 set) |
| Strings normalization | not planned | |
| Redundant `else` keyword | PHP CS Fixer (refactor) | `no_useless_else` |
| Inverted `if-else` constructs | not planned | |
| Unnecessary string case manipulation | not planned | |
| `array_unique(...)` can be used | not planned | |
| `compact(...)` can be used | not planned | |
| `isset(...)` constructs can be merged | not planned | |
| `strlen(...)` misused | custom (Task 6) | `phpInspections.strlenEmptinessCheck` |
| `ob_get_clean()` can be used | not planned | |
| **Language level migration** | | |
| Alias functions usage | PHP CS Fixer (refactor) | `no_alias_functions` |
| **Performance** | | |
| Packed hashtable optimizations | not planned | |
| Slow array function used in loop | custom (Task 7) | `phpInspections.arrayMergeInLoop` |
| Foreach variables reference usage correctness | not planned | |
| Non-optimized arrays mapping | not planned | |
| Non-optimal regular expression | not planned | |
| **Probable bugs** | | |
| Phar-incompatible `realpath()` usage | not planned | |
| Forgotten debug statements | not planned | |
| Proper `preg_quote()` usage | not planned | |
| `compact()` arguments existence | ergebnis | `ergebnis.noCompact` |
| Class autoloading correctness | not planned | |
| Null pointer exceptions prevention | PHPStan core | `method.nonObject`, `property.nonObject` |
| `mkdir(...)` race condition | not planned | |
| `file_put_contents(...)` race condition | not planned | |
| Continue misbehaviour in switch | ergebnis | `ergebnis.noSwitch` |
| Empty/isset results correctness | strict-rules | `empty.notAllowed` |
| Incorrect random generation range | not planned | |
| Suspicious loop | not planned | |
| **Security** | | |
| Exploiting unserialize | not planned | |
| Cryptographically secure algorithms | not planned | |
| Cryptographically secure randomness | not planned | |
| Variables extraction | not planned | |
| SSL server spoofing | not planned | |
| Security advisories | not planned | |
| RSA oracle padding vulnerability | not planned | |
| Basic malware patterns | not planned | |
| Untrusted files inclusion | not planned | |
| **Types compatibility** | | |
| `empty(...)` usage | strict-rules | `empty.notAllowed` |
| Strict comparison | strict-rules | `equal.notAllowed`, `notEqual.notAllowed` |
| Strict type search in arrays | PHP CS Fixer (refactor) | `strict_param` |
| JSON encoding API usage | not planned | |
| **Unused** | | |
| Parameter/variable is not used | Rector set | `RemoveUnusedVariableAssignRector` (dead code) |
| Useless unset | not planned | |
| Class property initialization flaws | not planned | |
| Unused constructor dependencies | PHPStan core | `property.onlyWritten`, `property.unused` |
| Senseless proxy function | not planned | |
| Child method is exactly the same | not planned | |
| Unnecessary isset arguments specification | not planned | |
| Unnecessary `date(...)` arguments specification | not planned | |
| **Not in the docs index** | | |
| Callable calls in loops termination condition | custom (Task 7) | `phpInspections.callInForCondition` |
| Count used to check emptiness | custom (Task 6) | `phpInspections.countEmptinessCheck` |
