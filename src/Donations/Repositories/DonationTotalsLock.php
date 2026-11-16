<?php

namespace Give\Donations\Repositories;

use Give\Framework\Database\DB;

/**
 * Marks that one request is computing a donation total, so others wait for it instead of running
 * the same query.
 *
 * The lock is an options row. INSERT IGNORE adds it only when no request holds it, which
 * add_option() cannot promise. A lock older than EXPIRES_AFTER seconds belongs to a request that
 * died and is taken over.
 *
 * @since TBD
 */
class DonationTotalsLock
{
    /**
     * @since TBD
     */
    public const EXPIRES_AFTER = 300;

    /**
     * @var string
     */
    private $name;

    /**
     * @since TBD
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Takes the lock. False when another request holds it.
     *
     * @since TBD
     */
    public function acquire(): bool
    {
        global $wpdb;

        $inserted = DB::query(DB::prepare(
            "INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'off')",
            $this->name,
            (string)time()
        ));

        if ($inserted) {
            return true;
        }

        if ($this->isHeld()) {
            return false;
        }

        DB::query(DB::prepare(
            "UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s",
            (string)time(),
            $this->name
        ));

        return true;
    }

    /**
     * Whether a live request holds the lock.
     *
     * @since TBD
     */
    public function isHeld(): bool
    {
        global $wpdb;

        $lockedAt = (int)DB::get_var(DB::prepare(
            "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
            $this->name
        ));

        return $lockedAt > time() - self::EXPIRES_AFTER;
    }

    /**
     * @since TBD
     */
    public function release(): void
    {
        global $wpdb;

        DB::query(DB::prepare("DELETE FROM {$wpdb->options} WHERE option_name = %s", $this->name));
    }
}
