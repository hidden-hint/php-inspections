<?php

declare(strict_types=1);

use HiddenHint\PhpInspections\Rector\CountEmptinessCheckToArrayComparisonRector;
use HiddenHint\PhpInspections\Rector\StrlenEmptinessCheckToStringComparisonRector;
use Rector\CodeQuality\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPhpSets()
    ->withPreparedSets(deadCode: true, codeQuality: true, typeDeclarations: true)
    ->withImportNames()
    ->withRules([
        CountEmptinessCheckToArrayComparisonRector::class,
        StrlenEmptinessCheckToStringComparisonRector::class,
    ])
    ->withSkip([StrlenZeroToIdenticalEmptyStringRector::class]);
