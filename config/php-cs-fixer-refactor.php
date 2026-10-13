<?php

declare(strict_types=1);

use PhpCsFixer\Config;

$formatConfig = require __DIR__ . '/php-cs-fixer.php';

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        ...$formatConfig->getRules(),
        'blank_line_after_opening_tag' => true,
        'declare_strict_types' => true,
        'final_class' => true,
        'static_lambda' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'no_alias_functions' => true,
        'is_null' => true,
        'modernize_strpos' => true,
        'ternary_to_null_coalescing' => true,
        'no_useless_else' => true,
    ]);
