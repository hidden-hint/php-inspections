<?php

declare(strict_types=1);

use PhpCsFixer\Config;

return (new Config())
    ->setRules([
        '@PER-CS' => true,
        'no_trailing_whitespace_in_comment' => false,
        'blank_line_before_statement' => ['statements' => ['return']],
        'single_blank_line_at_eof' => true,
        'yoda_style' => [
            'equal' => null,
            'identical' => null,
            'less_and_greater' => null,
        ],
    ]);
