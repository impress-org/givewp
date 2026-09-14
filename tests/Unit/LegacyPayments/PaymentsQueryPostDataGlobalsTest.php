<?php

namespace Give\Tests\Unit\LegacyPayments;

use Faker\Factory;
use Faker\Generator;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Payments_Query;

/**
 * @since TBD
 *
 * @covers Give_Payments_Query::get_payments
 */
class PaymentsQueryPostDataGlobalsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     *
     * @var Generator
     */
    private $faker;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        unset($GLOBALS['id']);

        parent::tearDown();
    }

    /**
     * Network Admin holds the site being edited in global $id and reads it back after
     * admin_enqueue_scripts, so a donation query run on that hook must leave it alone.
     *
     * @since TBD
     */
    public function testDoesNotLeaveTheDonationIdInTheGlobalScope(): void
    {
        Donation::factory()->create(['status' => DonationStatus::COMPLETE()]);

        $unrelatedId = $this->faker->numberBetween(1, 1000);
        $GLOBALS['id'] = $unrelatedId;

        $loopIterations = 0;
        add_filter(
            'give_payment',
            static function ($payment) use (&$loopIterations) {
                $loopIterations++;

                return $payment;
            }
        );

        $payments = (new Give_Payments_Query())->get_payments();

        /*
         * get_payments() returns a cached result without looping, in which case it cannot pollute
         * anything and the assertion below would hold for the wrong reason. Proving the loop ran is
         * what makes this a regression test.
         */
        $this->assertGreaterThan(0, $loopIterations, 'The results loop did not run, so nothing was exercised');
        $this->assertCount($loopIterations, $payments);
        $this->assertSame($unrelatedId, $GLOBALS['id']);
    }
}
