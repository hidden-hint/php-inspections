#!/usr/bin/env bash
set -euo pipefail

project_root="$(cd "$(dirname "$0")/.." && pwd)"
sample_project="$project_root/tests/smoke/sample-project"
config_file="$HOME/.config/php-inspections/phpstan.neon"
phpstan_binary="$(composer global config bin-dir --absolute 2>/dev/null)/phpstan"
expected_identifiers=(
    phpInspections.untypedClassConstant
    phpInspections.booleanFlagParameter
    phpInspections.abbreviatedName
    phpInspections.countEmptinessCheck
    phpInspections.arrayMergeInLoop
)

(cd "$sample_project" && "$phpstan_binary" clear-result-cache -c "$config_file" >/dev/null 2>&1) || true
report="$(cd "$sample_project" && "$phpstan_binary" analyse --error-format=json --no-progress -c "$config_file" 2>/dev/null || true)"

missing=0
for identifier in "${expected_identifiers[@]}"; do
    if jq -e --arg identifier "$identifier" '[.files[].messages[].identifier] | index($identifier) != null' <<<"$report" >/dev/null 2>&1; then
        echo "found:   $identifier"
    else
        echo "missing: $identifier"
        missing=1
    fi
done

exit "$missing"
