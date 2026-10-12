<?php

declare(strict_types=1);

$tmp = 1;
$tmp = 2;
foreach ([1, 2] as $val) {
}

function topLevelHelper(): int
{
    $res = 1;

    return $res;
}
