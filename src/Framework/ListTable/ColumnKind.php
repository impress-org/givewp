<?php

namespace Give\Framework\ListTable;

/**
 * What a list table column holds. Clients use it to pick a layout and a role for the column.
 *
 * @since TBD
 */
final class ColumnKind
{
    public const MONEY = 'money';
    public const DATE = 'date';
    public const COUNT = 'count';
    public const TEXT = 'text';
    public const STATUS = 'status';
}
