<?php

declare(strict_types=1);

$root = getcwd();

return ['parameters' => [
    'paths' => [$root],
    'excludePaths' => ['analyseAndScan' => [$root . '/vendor (?)']],
]];
