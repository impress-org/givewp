<?php

namespace Give\Donations\Storage;

use Give\Donations\ValueObjects\DonationStorageMode;
use InvalidArgumentException;

/**
 * The site-level switch that says which donation storage is authoritative. Every site starts on
 * posts. Once a site has left posts its give_donations table holds rows the posts do not, so the
 * flag can move back from table to syncing but never to posts.
 *
 * @since TBD
 */
class DonationStorageFlag
{
    const OPTION = 'give_donation_storage';

    /**
     * @since TBD
     */
    public static function get(): DonationStorageMode
    {
        $value = get_option(self::OPTION, DonationStorageMode::POSTS);

        return DonationStorageMode::isValid($value)
            ? new DonationStorageMode($value)
            : DonationStorageMode::POSTS();
    }

    /**
     * @since TBD
     *
     * @throws InvalidArgumentException when moving back to posts from any other state.
     */
    public static function set(DonationStorageMode $mode): void
    {
        if ($mode->isPosts() && !self::get()->isPosts()) {
            throw new InvalidArgumentException('Donation storage cannot move back to posts once it has left it.');
        }

        update_option(self::OPTION, $mode->getValue());
    }
}
