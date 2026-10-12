<?php

declare(strict_types=1);

use PhpCsFixer\Config;

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS' => true,
        'declare_strict_types' => true,
        'final_class' => true,
        'static_lambda' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'blank_line_before_statement' => ['statements' => ['return']],
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
        'single_blank_line_at_eof' => true,
        'yoda_style' => [
            'equal' => null,
            'identical' => null,
            'less_and_greater' => null,
        ],
    ]);
