<?php

namespace Give\Framework\QueryBuilder\Types;

/**
 * @unreleased
 */
class Operator extends Type
{
    const AND = 'AND';
    const OR = 'OR';
    const ON = 'ON';
    const BETWEEN = 'BETWEEN';
    const NOTBETWEEN = 'NOT BETWEEN';
    const EXISTS = 'EXISTS';
    const NOTEXISTS = 'NOT EXISTS';
    const IN = 'IN';
    const NOTIN = 'NOT IN';
    const LIKE = 'LIKE';
    const NOTLIKE = 'NOT LIKE';
    const NOT = 'NOT';
    const ISNULL = 'IS NULL';
    const NOTNULL = 'IS NOT NULL';
}
