<?php

namespace Give\Framework\QueryBuilder\Types;

/**
 * @unreleased
 */
class LogicalOperator extends Type
{
    const AND = 'AND';
    const OR = 'OR';
    const BETWEEN = 'BETWEEN';
    const RIGHT = 'RIGHT';
    const EXISTS = 'EXISTS';
    const IN = 'IN';
    const LIKE = 'LIKE';
    const NOT = 'NOT';
}
