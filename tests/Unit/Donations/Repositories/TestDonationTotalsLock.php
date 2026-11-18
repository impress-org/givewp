<?php

namespace Give\Tests\Unit\Donations\Repositories;

use Give\Donations\Repositories\DonationTotalsCache;
use Give\Donations\Repositories\DonationTotalsLock;
use Give\Tests\TestCase;

/**
 * The lock hands out one owner at a time, also when an expired lock is taken over by several requests.
 *
 * @since TBD
 */
class TestDonationTotalsLock extends TestCase
{
    private const NAME = 'givewp_test_totals_lock';

    /**
     * @var callable[]
     */
    private $filters = [];

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        (new DonationTotalsLock(self::NAME))->release();
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        foreach ($this->filters as $filter) {
            remove_filter('query', $filter, 1);
        }

        $this->filters = [];
        (new DonationTotalsLock(self::NAME))->release();

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testOnlyOneRequestHoldsTheLock(): void
    {
        $lock = new DonationTotalsLock(self::NAME);

        $this->assertTrue($lock->acquire());
        $this->assertFalse($lock->acquire());
        $this->assertTrue($lock->isHeld());

        $lock->release();

        $this->assertTrue($lock->acquire());
    }

    /**
     * @since TBD
     */
    public function testARequestThatOutlivedItsLockDoesNotReleaseTheNewHoldersLock(): void
    {
        $slow = new DonationTotalsLock(self::NAME, 300);
        $this->assertTrue($slow->acquire());

        // The slow request runs past the expiry and another request takes the lock over.
        $this->setLockedAtKeepingToken(time() - 1000);
        $newHolder = new DonationTotalsLock(self::NAME, 300);
        $this->assertTrue($newHolder->acquire());

        $slow->release();

        $this->assertTrue($newHolder->isHeld(), 'the slow request left the new holder\'s lock alone');
        $this->assertFalse((new DonationTotalsLock(self::NAME, 300))->acquire());
    }

    /**
     * @since TBD
     */
    public function testReleasingALockThisInstanceDoesNotHoldChangesNothing(): void
    {
        $holder = new DonationTotalsLock(self::NAME);
        $this->assertTrue($holder->acquire());

        (new DonationTotalsLock(self::NAME))->release();

        $this->assertTrue($holder->isHeld());
    }

    /**
     * @since TBD
     */
    public function testAnExpiredLockIsTakenOverOnce(): void
    {
        $this->setLockedAt(time() - 1000);
        $lock = new DonationTotalsLock(self::NAME, 300);

        $this->assertTrue($lock->acquire());
        $this->assertFalse($lock->acquire(), 'the takeover renewed the lock');
    }

    /**
     * @since TBD
     */
    public function testTheLockLosesATakeoverAnotherRequestWonBetweenItsReadAndItsUpdate(): void
    {
        $this->setLockedAt(time() - 1000);
        $lock = new DonationTotalsLock(self::NAME, 300);

        // The other request renews the lock after this one read the expired value.
        $this->onceBefore('UPDATE', function () {
            $this->setLockedAt(time());
        });

        $this->assertFalse($lock->acquire());
    }

    /**
     * @since TBD
     */
    public function testTheLockIsInsertedAgainWhenItWasReleasedAfterTheFirstInsertFailed(): void
    {
        $holder = new DonationTotalsLock(self::NAME);
        $holder->acquire();
        $lock = new DonationTotalsLock(self::NAME);

        // The holder releases the lock after this request's INSERT IGNORE found it.
        $this->onceBefore('SELECT option_value', static function () use ($holder) {
            $holder->release();
        });

        $this->assertTrue($lock->acquire());
        $this->assertTrue($lock->isHeld(), 'a row exists, so a third request cannot take the lock');
        $this->assertFalse((new DonationTotalsLock(self::NAME))->acquire());
    }

    /**
     * @since TBD
     */
    public function testRememberTakesOverAndReleasesTheLockWhenTheHolderStopsWithoutAValue(): void
    {
        $key = 'givewp-test-key';
        $hash = md5($key);
        $lockName = DonationTotalsCache::OPTION . '_lock_' . $hash;
        $holder = new DonationTotalsLock($lockName);
        $holder->acquire();

        // The holder dies during the first wait without storing a value.
        // The first read is the lock's own (it finds the live holder); the second is the wait loop's.
        $this->onceBefore('SELECT option_value', static function () use ($holder) {
            $holder->release();
        }, 2);

        $isLockedWhileComputing = null;
        $value = DonationTotalsCache::remember($key, static function () use (&$isLockedWhileComputing, $holder) {
            $isLockedWhileComputing = $holder->isHeld();

            return 42;
        });

        $this->assertSame(42, $value);
        $this->assertTrue($isLockedWhileComputing, 'the request took the lock before it computed');
        $this->assertFalse($holder->isHeld(), 'and released its own lock');
    }

    /**
     * @since TBD
     */
    private function setLockedAtKeepingToken(int $time): void
    {
        global $wpdb;

        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->options} SET option_value = CONCAT(%s, SUBSTRING(option_value, LOCATE(':', option_value))) WHERE option_name = %s",
            (string)$time,
            self::NAME
        ));
    }

    /**
     * @since TBD
     */
    private function setLockedAt(int $time): void
    {
        global $wpdb;

        $wpdb->query($wpdb->prepare(
            "INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'off')
             ON DUPLICATE KEY UPDATE option_value = %s",
            self::NAME,
            (string)$time,
            (string)$time
        ));
    }

    /**
     * Runs the callback once, just before the nth statement that starts with the prefix and names a
     * lock row.
     *
     * @since TBD
     */
    private function onceBefore(string $prefix, callable $callback, int $nth = 1): void
    {
        $done = false;
        $seen = 0;
        $filter = static function ($query) use ($prefix, $nth, &$done, &$seen, $callback) {
            if (!$done && stripos($query, $prefix) === 0 && strpos($query, '_lock') !== false && ++$seen === $nth) {
                $done = true;
                $callback();
            }

            return $query;
        };

        add_filter('query', $filter, 1);
        $this->filters[] = $filter;
    }
}
