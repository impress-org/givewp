<?php

namespace Give\Donors\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;
use Give\Framework\Support\ValueObjects\EnumInteractsWithQueryBuilder;

/**
 * @since 2.19.6
 *
 * @method static FIRST_NAME()
 * @method static LAST_NAME()
 * @method static ADDITIONAL_EMAILS()
 * @method static PREFIX()
 */
class DonorMetaKeys extends Enum
{
    use EnumInteractsWithQueryBuilder;

    const FIRST_NAME = '_give_donor_first_name';
    const LAST_NAME = '_give_donor_last_name';
    const ADDITIONAL_EMAILS = 'additional_email';
    const PREFIX = '_give_donor_title_prefix';

    /**
     * @since 2.24.0 change function to remove ADDITIONAL_EMAILS from columns
     *
     * @return array
     */
    public static function getColumnsForAttachMetaQueryWithoutAdditionalEmails()
    {
        $columns = self::getColumnsForAttachMetaQuery();


        $id = array_search(
            [self::ADDITIONAL_EMAILS, self::ADDITIONAL_EMAILS()->getKeyAsCamelCase()],
            $columns,
            true
        );

        unset($columns[$id]);

        return $columns;
    }
}
