<?php

declare(strict_types=1);

use HiddenHint\PhpInspections\Rector\StrlenEmptinessCheckToStringComparisonRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()->withRules([StrlenEmptinessCheckToStringComparisonRector::class]);
