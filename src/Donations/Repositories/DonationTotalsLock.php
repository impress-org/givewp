<?php

namespace Give\Donations\Repositories;

use Give\Framework\Database\DB;

/**
 * Marks that one request is computing a donation total, so others wait for it instead of running
 * the same query.
 *
 * The lock is an options row. INSERT IGNORE adds it only when no request holds it, which
 * add_option() cannot promise. A lock older than EXPIRES_AFTER seconds belongs to a request that
 * died and is taken over; a caller that runs longer can pass its own expiry. The row holds the time
 * and a token of its holder, and release() removes only a row that still holds that token, so a
 * request that outlived its lock cannot remove the lock of the request that took it over.
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
     * @var int
     */
    private $expiresAfter;

    /**
     * The value of the row this instance wrote when it took the lock, null when it holds none.
     *
     * @var string|null
     */
    private $token = null;

    /**
     * @since TBD
     *
     * @param int $expiresAfter seconds after which a held lock counts as abandoned
     */
    public function __construct(string $name, int $expiresAfter = self::EXPIRES_AFTER)
    {
        $this->name = $name;
        $this->expiresAfter = $expiresAfter;
    }

    /**
     * Takes the lock. False when another request holds it.
     *
     * @since TBD
     */
    public function acquire(): bool
    {
        $token = time() . ':' . wp_generate_uuid4();

        if (!$this->take($token)) {
            return false;
        }

        $this->token = $token;

        return true;
    }

    /**
     * @since TBD
     */
    private function take(string $token): bool
    {
        global $wpdb;

        $inserted = DB::query(DB::prepare(
            "INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'off')",
            $this->name,
            $token
        ));

        if ($inserted) {
            return true;
        }

        $lockedAt = DB::get_var(DB::prepare(
            "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
            $this->name
        ));

        if ($lockedAt === null) {
            // Released between the INSERT IGNORE and this read: insert again, there is no row to update.
            return (bool)DB::query(DB::prepare(
                "INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'off')",
                $this->name,
                $token
            ));
        }

        if ((int)$lockedAt > time() - $this->expiresAfter) {
            return false;
        }

        // Only one request can change the row from the expired value it read.
        return (int)DB::query(DB::prepare(
            "UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s AND option_value = %s",
            $token,
            $this->name,
            (string)$lockedAt
        )) === 1;
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

        return $lockedAt > time() - $this->expiresAfter;
    }

    /**
     * Gives the lock up. Removes the row only when it still holds this instance's token, and does
     * nothing when the instance does not hold the lock.
     *
     * @since TBD
     */
    public function release(): void
    {
        global $wpdb;

        if ($this->token === null) {
            return;
        }

        DB::query(DB::prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s",
            $this->name,
            $this->token
        ));

        $this->token = null;
    }
}
