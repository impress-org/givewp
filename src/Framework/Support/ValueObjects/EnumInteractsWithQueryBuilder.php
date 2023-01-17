<?php

namespace Give\Framework\Support\ValueObjects;

use Give\Framework\Support\Facades\Str;

trait EnumInteractsWithQueryBuilder
{
    /**
     * @since 2.19.6
     *
     * Returns array of meta aliases to be used with attachMeta
     *
     * [ ['_give_payment_total', 'amount'], etc. ]
     *
     * @return array
     */
    public static function getColumnsForAttachMetaQuery()
    {
        $columns = [];

        foreach (static::toArray() as $key => $value) {
            $keyFormatted = Str::camel($key);

            $columns[] = [$value, $keyFormatted];
        }

        return $columns;
    }

    /**
     * @since 2.24.0
     *
     * Returns array of meta aliases to be used with attachMeta based on the given array of ENUMs
     *
     * [ ['_give_payment_total', 'amount'], etc. ]
     *
     * @param array<Enum> $enums An array of Enums. Eg.: [ DonationMetaKeys::AMOUNT(), etc. ]
     *
     * @return array
     */
    public static function getColumnsForAttachMetaQueryFromArray(array $enums): array
    {
        $columns = [];

        foreach ($enums as $enum) {
            $value = $enum->getValue();
            $keyFormatted = $enum->getKeyAsCamelCase();

            $columns[] = [$value, $keyFormatted];
        }

        return $columns;
    }
}
