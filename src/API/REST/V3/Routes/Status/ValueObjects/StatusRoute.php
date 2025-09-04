<?php declare(strict_types=1);

namespace Give\API\REST\V3\Routes\Status\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @tbd
 *
 * @method static StatusRoute NAMESPACE()
 * @method static StatusRoute BASE()
 * @method bool isNamespace()
 * @method bool isBase()
 */
class StatusRoute extends Enum
{
    public const NAMESPACE = 'givewp/v3';
    public const BASE = 'status';
}
