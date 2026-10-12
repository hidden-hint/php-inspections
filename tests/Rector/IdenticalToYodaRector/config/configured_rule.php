<?php

declare(strict_types=1);

use HiddenHint\PhpInspections\Rector\IdenticalToYodaRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()->withRules([IdenticalToYodaRector::class]);
