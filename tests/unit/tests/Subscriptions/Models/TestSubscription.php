<?php

namespace unit\tests\Subscriptions\Models;

use Give\Framework\Models\Traits\InteractsWithTime;
use PHPUnit\Framework\TestCase;

/**
 * @unreleased
 *
 * @coversDefaultClass \Give\Subscriptions\Models\Subscription
 */
class TestSubscription extends TestCase
{
    use InteractsWithTime;

    /**
     * @return void
     */
    public function testSubscriptionShouldGetDonations()
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testSubscriptionShouldGetDonor()
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testSubscriptionShouldGetNotes()
    {
        $this->markTestIncomplete();
    }
}
