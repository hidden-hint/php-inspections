<?php

declare(strict_types=1);

use HiddenHint\PhpInspections\Rector\CountEmptinessCheckToArrayComparisonRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()->withRules([CountEmptinessCheckToArrayComparisonRector::class]);
