<?php

namespace Give\Donors\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;
use Give\Framework\Support\ValueObjects\EnumInteractsWithQueryBuilder;

/**
 * @since 4.4.0 Add AVATAR_ID and COMPANY
 * @since 2.19.6
 *
 * @method static DonorMetaKeys FIRST_NAME()
 * @method static DonorMetaKeys LAST_NAME()
 * @method static DonorMetaKeys ADDITIONAL_EMAILS()
 * @method static DonorMetaKeys ADDRESS_LINE1()
 * @method static DonorMetaKeys ADDRESS_LINE2()
 * @method static DonorMetaKeys ADDRESS_CITY()
 * @method static DonorMetaKeys ADDRESS_STATE()
 * @method static DonorMetaKeys ADDRESS_COUNTRY()
 * @method static DonorMetaKeys ADDRESS_ZIP()
 * @method static DonorMetaKeys PREFIX()
 * @method static DonorMetaKeys AVATAR_ID()
 * @method static DonorMetaKeys COMPANY()
 */
class DonorMetaKeys extends Enum
{
    use EnumInteractsWithQueryBuilder;

    const FIRST_NAME = '_give_donor_first_name';
    const LAST_NAME = '_give_donor_last_name';
    const ADDITIONAL_EMAILS = 'additional_email';
    const ADDRESS_LINE1 = '_give_donor_address_billing_line1_';
    const ADDRESS_LINE2 = '_give_donor_address_billing_line2_';
    const ADDRESS_CITY = '_give_donor_address_billing_city_';
    const ADDRESS_STATE = '_give_donor_address_billing_state_';
    const ADDRESS_COUNTRY = '_give_donor_address_billing_country_';
    const ADDRESS_ZIP = '_give_donor_address_billing_zip_';
    const PREFIX = '_give_donor_title_prefix';
    const AVATAR_ID = '_give_donor_avatar_id';
    const COMPANY = '_give_donor_company';

    /**
     * @since 4.4.0 Remove address meta keys from columns
     * @since 2.24.0 change function to remove ADDITIONAL_EMAILS from columns
     *
     * @return array
     */
    public static function getColumnsForAttachMetaQueryWithoutExtraMetadata()
    {
        $columns = self::getColumnsForAttachMetaQuery();

        $metaKeysToRemove = [
            self::ADDITIONAL_EMAILS,
            self::ADDRESS_LINE1,
            self::ADDRESS_LINE2,
            self::ADDRESS_CITY,
            self::ADDRESS_STATE,
            self::ADDRESS_COUNTRY,
            self::ADDRESS_ZIP,
        ];

        foreach ($metaKeysToRemove as $metaKey) {
            $camelCaseKey = (new self($metaKey))->getKeyAsCamelCase();

            foreach ($columns as $index => $column) {
                if (is_array($column) && isset($column[1]) && $column[1] === $camelCaseKey) {
                    unset($columns[$index]);
                    break;
                }
            }
        }

        $columns = array_values($columns);

        return $columns;
    }
}
