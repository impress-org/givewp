<?php

namespace Give\Tests\Unit\LegacySubscriptions;

use Give\Donations\Models\Donation;
use Give\Donors\Models\Donor;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Subscription;
use Give_Subscriptions_DB;

/**
 * Covers the queries of Give_Subscriptions_DB and Give_Subscription::payment_exists().
 *
 * The tests for a quote in a profile id, a transaction id or a status, for an unknown groupBy
 * value, and for a bad order value were added after the SQL fix, because they check the new
 * hardening of generate_where_clause(), count() and get_subscriptions(). Every other test was
 * written first and passed on the unchanged code.
 *
 * @since TBD
 */
class SubscriptionsDBTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    private function db(): Give_Subscriptions_DB
    {
        return new Give_Subscriptions_DB();
    }

    /**
     * @since TBD
     */
    private function createSubscription(
        string $status,
        string $profileId,
        string $transactionId,
        ?int $donorId = null
    ): Subscription {
        $attributes = [
            'status' => new SubscriptionStatus($status),
            'gatewaySubscriptionId' => $profileId,
            'transactionId' => $transactionId,
        ];

        if ($donorId) {
            $attributes['donorId'] = $donorId;
        }

        return Subscription::factory()->create($attributes);
    }

    /**
     * @since TBD
     *
     * @param Give_Subscription[] $subscriptions
     *
     * @return int[]
     */
    private function ids(array $subscriptions): array
    {
        $ids = array_map(static function ($subscription) {
            return (int)$subscription->id;
        }, $subscriptions);

        sort($ids);

        return $ids;
    }

    /**
     * @since TBD
     */
    public function testStatusAsStringAndAsArray()
    {
        $active = $this->createSubscription('active', 'profile_a', 'txn_a');
        $cancelled = $this->createSubscription('cancelled', 'profile_b', 'txn_b');
        $this->createSubscription('pending', 'profile_c', 'txn_c');

        $this->assertSame([$active->id], $this->ids($this->db()->get_subscriptions(['status' => 'active'])));
        $this->assertSame(
            [$active->id, $cancelled->id],
            $this->ids($this->db()->get_subscriptions(['status' => ['active', 'cancelled']]))
        );
    }

    /**
     * @since TBD
     */
    public function testProfileIdAsStringAndAsArray()
    {
        $first = $this->createSubscription('active', 'profile_a', 'txn_a');
        $second = $this->createSubscription('active', 'profile_b', 'txn_b');
        $this->createSubscription('active', 'profile_c', 'txn_c');

        $this->assertSame([$first->id], $this->ids($this->db()->get_subscriptions(['profile_id' => 'profile_a'])));
        $this->assertSame(
            [$first->id, $second->id],
            $this->ids($this->db()->get_subscriptions(['profile_id' => ['profile_a', 'profile_b']]))
        );
    }

    /**
     * @since TBD
     */
    public function testTransactionIdAsStringAndAsArray()
    {
        $first = $this->createSubscription('active', 'profile_a', 'txn_a');
        $second = $this->createSubscription('active', 'profile_b', 'txn_b');
        $this->createSubscription('active', 'profile_c', 'txn_c');

        $this->assertSame([$first->id], $this->ids($this->db()->get_subscriptions(['transaction_id' => 'txn_a'])));
        $this->assertSame(
            [$first->id, $second->id],
            $this->ids($this->db()->get_subscriptions(['transaction_id' => ['txn_a', 'txn_b']]))
        );
    }

    /**
     * @since TBD
     */
    public function testSearchByTransactionIdAndProfileId()
    {
        $first = $this->createSubscription('active', 'profile_a', 'txn_a');
        $this->createSubscription('active', 'profile_b', 'txn_b');

        $this->assertSame([$first->id], $this->ids($this->db()->get_subscriptions(['search' => 'txn:txn_a'])));
        $this->assertSame(
            [$first->id],
            $this->ids($this->db()->get_subscriptions(['search' => 'profile_id:profile_a']))
        );
    }

    /**
     * A value with a quote used to work in the search, because the search escaped it.
     *
     * @since TBD
     */
    public function testSearchByTransactionIdWithAQuote()
    {
        $match = $this->createSubscription('active', 'profile_a', "txn_O'Brien");
        $this->createSubscription('active', 'profile_b', 'txn_b');

        $this->assertSame([$match->id], $this->ids($this->db()->get_subscriptions(['search' => "txn:txn_O'Brien"])));
        $this->assertSame('', $GLOBALS['wpdb']->last_error);
    }

    /**
     * @since TBD
     */
    public function testSearchByDonorName()
    {
        $donor = Donor::factory()->create(['name' => "Ann O'Brien Smith"]);
        $match = $this->createSubscription('active', 'profile_a', 'txn_a', $donor->id);
        $this->createSubscription('active', 'profile_b', 'txn_b');

        $this->assertSame([$match->id], $this->ids($this->db()->get_subscriptions(['search' => "O'Brien"])));
        $this->assertSame([$match->id], $this->ids($this->db()->get_subscriptions(['search' => 'Ann'])));
        $this->assertSame('', $GLOBALS['wpdb']->last_error);
    }

    /**
     * @since TBD
     */
    public function testOrderAscendingAndDescending()
    {
        $first = $this->createSubscription('active', 'profile_a', 'txn_a');
        $second = $this->createSubscription('active', 'profile_b', 'txn_b');

        $descending = array_map(static function ($subscription) {
            return (int)$subscription->id;
        }, $this->db()->get_subscriptions(['order' => 'DESC']));
        $ascending = array_map(static function ($subscription) {
            return (int)$subscription->id;
        }, $this->db()->get_subscriptions(['order' => 'ASC']));

        $this->assertSame([$second->id, $first->id], $descending);
        $this->assertSame([$first->id, $second->id], $ascending);
    }

    /**
     * @since TBD
     */
    public function testCountWithoutAndWithGroupBy()
    {
        $this->createSubscription('active', 'profile_a', 'txn_a');
        $this->createSubscription('active', 'profile_b', 'txn_b');
        $this->createSubscription('cancelled', 'profile_c', 'txn_c');

        $this->assertSame(3, $this->db()->count());
        $this->assertSame(2, $this->db()->count(['status' => 'active']));
        $this->assertEquals(
            ['active' => 2, 'cancelled' => 1],
            $this->db()->count(['groupBy' => 'status'])
        );
    }

    /**
     * @since TBD
     */
    public function testGenerateWhereClauseForNormalInput()
    {
        $where = $this->db()->generate_where_clause(
            [
                'status' => ['active', 'cancelled'],
                'profile_id' => 'profile_a',
                'transaction_id' => ['txn_a', 'txn_b'],
            ]
        );

        $this->assertSame(
            " WHERE 1=1 AND `profile_id` IN( 'profile_a' )  AND `transaction_id` IN( 'txn_a','txn_b' )  AND `status` IN( 'active','cancelled' ) ",
            $where
        );
    }

    /**
     * @since TBD
     */
    public function testGenerateWhereClauseForSingleStringValues()
    {
        $where = $this->db()->generate_where_clause(
            [
                'status' => 'active',
                'profile_id' => 'profile_a',
                'transaction_id' => 'txn_a',
            ]
        );

        $this->assertSame(
            " WHERE 1=1 AND `profile_id` IN( 'profile_a' )  AND `transaction_id` IN( 'txn_a' )  AND `status` = 'active' ",
            $where
        );
    }

    /**
     * Hardening test: before the fix a quote in a profile id broke the SQL.
     *
     * @since TBD
     */
    public function testProfileIdWithAQuoteDoesNotBreakTheQuery()
    {
        $match = $this->createSubscription('active', "profile_O'Brien", 'txn_a');
        $this->createSubscription('active', 'profile_b', 'txn_b');

        $this->assertSame(
            [$match->id],
            $this->ids($this->db()->get_subscriptions(['profile_id' => "profile_O'Brien"]))
        );
        $this->assertSame([], $this->db()->get_subscriptions(['profile_id' => "x'y"]));
        $this->assertSame([], $this->db()->get_subscriptions(['profile_id' => ["x'y", "z' OR '1'='1"]]));
        $this->assertSame('', $GLOBALS['wpdb']->last_error);
    }

    /**
     * Hardening test: before the fix a quote in a transaction id broke the SQL.
     *
     * @since TBD
     */
    public function testTransactionIdWithAQuoteDoesNotBreakTheQuery()
    {
        $match = $this->createSubscription('active', 'profile_a', "txn_O'Brien");
        $this->createSubscription('active', 'profile_b', 'txn_b');

        $this->assertSame(
            [$match->id],
            $this->ids($this->db()->get_subscriptions(['transaction_id' => "txn_O'Brien"]))
        );
        $this->assertSame([], $this->db()->get_subscriptions(['transaction_id' => "x' OR '1'='1"]));
        $this->assertSame('', $GLOBALS['wpdb']->last_error);
    }

    /**
     * Hardening test: before the fix a quote in a status broke the SQL.
     *
     * @since TBD
     */
    public function testStatusWithAQuoteDoesNotBreakTheQuery()
    {
        $this->createSubscription('active', 'profile_a', 'txn_a');

        $this->assertSame([], $this->db()->get_subscriptions(['status' => "x' OR '1'='1"]));
        $this->assertSame([], $this->db()->get_subscriptions(['status' => ["x'y", 'nope']]));
        $this->assertSame('', $GLOBALS['wpdb']->last_error);
    }

    /**
     * Hardening test: an unknown groupBy value is ignored, so the count is a plain number.
     *
     * @since TBD
     */
    public function testCountIgnoresAGroupByThatIsNotAColumn()
    {
        $this->createSubscription('active', 'profile_a', 'txn_a');
        $this->createSubscription('cancelled', 'profile_b', 'txn_b');

        $this->assertSame(2, $this->db()->count(['groupBy' => 'status; DROP TABLE x']));
        $this->assertSame(2, $this->db()->count(['groupBy' => 'status, (SELECT 1)']));
        $this->assertSame('', $GLOBALS['wpdb']->last_error);
    }

    /**
     * Hardening test: an order value that is not ASC or DESC falls back to DESC.
     *
     * @since TBD
     */
    public function testOrderThatIsNotAscOrDescFallsBackToDescending()
    {
        $first = $this->createSubscription('active', 'profile_a', 'txn_a');
        $second = $this->createSubscription('active', 'profile_b', 'txn_b');

        $ids = array_map(static function ($subscription) {
            return (int)$subscription->id;
        }, $this->db()->get_subscriptions(['order' => 'ASC; DROP TABLE x']));

        $this->assertSame([$second->id, $first->id], $ids);
        $this->assertSame('', $GLOBALS['wpdb']->last_error);
    }

    /**
     * @since TBD
     */
    public function testGetRenewingAndExpiringSubscriptionsRun()
    {
        $this->createSubscription('active', 'profile_a', 'txn_a');

        $renewing = $this->db()->get_renewing_subscriptions('+1month');
        $expiring = $this->db()->get_expiring_subscriptions('+1month');

        $this->assertIsArray($renewing);
        $this->assertIsArray($expiring);
        $this->assertSame('', $GLOBALS['wpdb']->last_error);
    }

    /**
     * @since TBD
     */
    public function testPaymentExistsFindsAKnownTransactionId()
    {
        Donation::factory()->create(['gatewayTransactionId' => 'txn_known']);
        Donation::factory()->create(['gatewayTransactionId' => "txn_O'Brien"]);

        $subscription = new Give_Subscription();

        $this->assertTrue($subscription->payment_exists('txn_known'));
        $this->assertTrue($subscription->payment_exists("txn_O'Brien"));
        $this->assertFalse($subscription->payment_exists('txn_unknown'));
        $this->assertFalse($subscription->payment_exists("txn_un'known"));
        $this->assertFalse($subscription->payment_exists(''));
    }
}
