<?php

namespace Give\Framework\QueryBuilder\Types;

/**
 * @unreleased
 */
class Operator extends Type
{
    const AND = 'AND';
    const OR = 'OR';
    const BETWEEN = 'BETWEEN';
    const NOTBETWEEN = 'NOT BETWEEN';
    const EXISTS = 'EXISTS';
    const IN = 'IN';
    const NOTIN = 'NOT IN';
    const LIKE = 'LIKE';
    const NOTLIKE = 'NOT LIKE';
    const NOT = 'NOT';
}
