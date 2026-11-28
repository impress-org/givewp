<?php

namespace Give\Campaigns\Repositories;

/**
 * The short-lived cache of the campaign stats in test mode. The live stats keep their own options.
 *
 * It is stored under a token. Clearing removes the token and the next read makes a new one, so nothing reads
 * the old figures again, also when a request that is still counting writes them afterwards. Some changes fire no hook (raw SQL tools), so the
 * cache also expires.
 *
 * @since TBD
 *
 * @internal
 */
final class TestModeStatsCache
{
    const TOKEN_OPTION = 'give_campaigns_test_stats_token';

    const LIFETIME_IN_SECONDS = 300;

    /**
     * @var string
     */
    private $key;

    /**
     * @since TBD
     */
    private function __construct(string $token)
    {
        $this->key = 'give_campaigns_test_stats_' . md5($token);
    }

    /**
     * Opens the cache before the stats are counted, so a change made while counting clears what this
     * request writes.
     *
     * @since TBD
     */
    public static function open(): self
    {
        $token = get_option(self::TOKEN_OPTION);

        if (!$token) {
            add_option(self::TOKEN_OPTION, wp_generate_uuid4(), '', false);
            $token = get_option(self::TOKEN_OPTION);
        }

        return new self((string)$token);
    }

    /**
     * @since TBD
     *
     * @return array{expires: int, donations: array, subscriptions: array}
     */
    public function read(): array
    {
        $stored = get_transient($this->key);

        if (is_array($stored) && ($stored['expires'] ?? 0) > time()) {
            return $stored;
        }

        return ['expires' => time() + self::LIFETIME_IN_SECONDS, 'donations' => [], 'subscriptions' => []];
    }

    /**
     * Keeps the expiry the figures got when first stored, so adding a campaign does not renew them all.
     *
     * @since TBD
     */
    public function write(array $stored): void
    {
        set_transient($this->key, $stored, max(1, $stored['expires'] - time()));
    }

    /**
     * Drops the cached figures. With nothing cached, as on a live site, it writes nothing.
     *
     * @since TBD
     */
    public static function clear(): void
    {
        if (get_option(self::TOKEN_OPTION) === false) {
            return;
        }

        delete_option(self::TOKEN_OPTION);
    }
}
