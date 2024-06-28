<?php
namespace Give\Tests\Framework\Support\ValueObjects\Enum;

use Give\Framework\Support\ValueObjects\BaseEnum as Enum;

/**
 * Class EnumConflict
 *
 * Forked from https://github.com/myclabs/php-enum
 *
 * @method static EnumConflict FOO()
 * @method static EnumConflict BAR()
 *
 * @author Daniel Costa <danielcosta@gmail.com>
 * @author Mirosław Filip <mirfilip@gmail.com>
 */
class EnumConflict extends Enum
{
    const FOO = "foo";
    const BAR = "bar";
}
