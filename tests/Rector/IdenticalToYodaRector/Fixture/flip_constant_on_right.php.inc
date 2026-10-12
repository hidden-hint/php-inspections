<?php

namespace HiddenHint\PhpInspections\Tests\Rector\IdenticalToYodaRector\Fixture;

final class FlipConstantOnRight
{
    private const string FOO = 'foo';

    public function run(mixed $value): array
    {
        return [$value === null, $value === self::FOO, $value === [], $value === -1];
    }
}

?>
-----
<?php

namespace HiddenHint\PhpInspections\Tests\Rector\IdenticalToYodaRector\Fixture;

final class FlipConstantOnRight
{
    private const string FOO = 'foo';

    public function run(mixed $value): array
    {
        return [null === $value, self::FOO === $value, [] === $value, -1 === $value];
    }
}

?>
