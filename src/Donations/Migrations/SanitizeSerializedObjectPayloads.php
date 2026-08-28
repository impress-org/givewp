<?php

namespace Give\Donations\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Helpers\Utils;

/**
 * @since 4.16.7.2
 */
class SanitizeSerializedObjectPayloads extends Migration
{
    /**
     * @since 4.16.7.2
     */
    public function run()
    {
        $this->sanitizeUserMeta();
        $this->sanitizeDonorMeta();
        $this->sanitizeDonationMeta();
        $this->sanitizeSessions();
    }

    /**
     * @since 4.16.7.2
     */
    private function sanitizeUserMeta()
    {
        $this->sanitizeTable(
            'usermeta',
            'umeta_id',
            'meta_key',
            'meta_value',
            ['first_name', 'last_name', 'user_title', 'billing_address']
        );
    }

    /**
     * @since 4.16.7.2
     */
    private function sanitizeDonorMeta()
    {
        $this->sanitizeTable(
            'give_donormeta',
            'meta_id',
            'meta_key',
            'meta_value',
            ['_give_donor_first_name', '_give_donor_last_name', '_give_donor_title_prefix']
        );
    }

    /**
     * @since 4.16.7.2
     */
    private function sanitizeDonationMeta()
    {
        $this->sanitizeTable(
            'give_donationmeta',
            'meta_id',
            'meta_key',
            'meta_value',
            ['_give_payment_meta', '_give_donor_billing_first_name', '_give_donor_billing_last_name']
        );
    }

    /**
     * @since 4.16.7.2
     */
    private function sanitizeSessions()
    {
        $this->sanitizeTable(
            'give_sessions',
            'session_id',
            'session_key',
            'session_value',
            []
        );
    }

    /**
     * @since 4.16.7.2
     *
     * @param string $table
     * @param string $idColumn
     * @param string $keyColumn
     * @param string $valueColumn
     * @param array  $keys
     */
    private function sanitizeTable($table, $idColumn, $keyColumn, $valueColumn, array $keys)
    {
        $query = DB::table($table);

        if ( ! empty($keys)) {
            $query->whereIn($keyColumn, $keys);
        }

        $rows = $query->getAll();

        foreach ($rows as $row) {
            if ( ! Utils::isSerialized($row->{$valueColumn})) {
                continue;
            }

            $safe = $this->sanitizeRecursively($row->{$valueColumn});

            DB::table($table)
                ->where($idColumn, $row->{$idColumn})
                ->update([
                    $valueColumn => maybe_serialize($safe),
                ]);
        }
    }

    /**
     * Recursively unserialize values and replace any object (including
     * __PHP_Incomplete_Class instances) with an empty string so the final
     * serialization cannot restore the original class.
     *
     * @since 4.16.7.2
     *
     * @param mixed $value
     * @return mixed
     */
    private function sanitizeRecursively($value)
    {
        if (is_string($value) && Utils::isSerialized($value)) {
            $unserialized = unserialize($value, ['allowed_classes' => false]);

            if (false !== $unserialized && $unserialized !== $value) {
                $value = $this->sanitizeRecursively($unserialized);
            }
        }

        if (is_object($value)) {
            return '';
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->sanitizeRecursively($item);
            }
        }

        return $value;
    }

    /**
     * @since 4.16.7.2
     */
    public static function id()
    {
        return 'sanitize-serialized-object-payloads';
    }

    /**
     * @since 4.16.7.2
     */
    public static function title()
    {
        return 'Sanitize serialized object payloads from meta tables and sessions';
    }

    /**
     * @since 4.16.7.2
     */
    public static function timestamp()
    {
        return strtotime('2026-08-26');
    }
}
