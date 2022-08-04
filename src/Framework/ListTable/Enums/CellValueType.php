<?php

declare(strict_types=1);

namespace Give\Framework\ListTable\Enums;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @method bool isSimple()
 * @method bool isMarkup()
 * @method bool isDateTime()
 * @method bool isCurrency()
 * @method static CellValueType SIMPLE()
 * @method static CellValueType MARKUP()
 * @method static CellValueType DATE_TIME()
 * @method static CellValueType CURRENCY()
 */
class CellValueType extends Enum
{
    const SIMPLE = 'simple';
    const MARKUP = 'markup';
    const DATE_TIME = 'datetime';
    const CURRENCY = 'currency';
}
