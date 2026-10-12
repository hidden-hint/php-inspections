<?php

declare(strict_types=1);

use PhpCsFixer\Finder;

return (require __DIR__ . '/config/php-cs-fixer.php')
    ->setFinder(
        (new Finder())
            ->in([__DIR__ . '/src', __DIR__ . '/tests'])
            ->exclude(['CodingStandard/fixtures'])
            ->notPath(['#/data/#', '#/Fixture/#']),
    );
