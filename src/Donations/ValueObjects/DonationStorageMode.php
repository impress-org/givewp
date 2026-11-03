<?php

namespace Give\Donations\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * Which donation storage a site reads from. Posts is the give_payment post type and its
 * give_donationmeta rows. Syncing keeps the give_donations table in step with posts while posts
 * stay authoritative for reads. Table means reads come from give_donations.
 *
 * @since TBD
 *
 * @method static DonationStorageMode POSTS()
 * @method static DonationStorageMode SYNCING()
 * @method static DonationStorageMode TABLE()
 * @method bool isPosts()
 * @method bool isSyncing()
 * @method bool isTable()
 */
class DonationStorageMode extends Enum
{
    const POSTS = 'posts';
    const SYNCING = 'syncing';
    const TABLE = 'table';
}
