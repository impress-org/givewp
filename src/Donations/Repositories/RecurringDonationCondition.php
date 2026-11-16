<?php

namespace Give\Donations\Repositories;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * The one definition of a recurring donation for the donation lists and their stats: a donation
 * meta row for the subscription ID with a value of "1" or more.
 *
 * Subscription IDs are positive integers, and a one-time donation stores 0 or nothing. The value
 * column is a prefix index, so `NOT IN ('', '0')`, `> '0'` or `<> '0'` still read every "0" row to
 * check it: on a site where every donation stores 0 that is a full pass over the key. The range
 * `>= '1'` starts after those rows and reads only the recurring ones. It leaves out values that no
 * writer stores: one that starts with "0" and goes on ("05"), and one that sorts before "0" (a
 * minus sign, a leading space).
 *
 * @since TBD
 */
class RecurringDonationCondition
{
    /**
     * Adds the condition on a donation meta table.
     *
     * @since TBD
     *
     * @param string $prefix table alias with a trailing dot, or an empty string
     */
    public static function apply(QueryBuilder $query, string $prefix = ''): QueryBuilder
    {
        return $query
            ->where("{$prefix}meta_key", DonationMetaKeys::SUBSCRIPTION_ID)
            ->where("{$prefix}meta_value", '1', '>=');
    }
}
