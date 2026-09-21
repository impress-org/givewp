<?php

namespace Give\Tests\Unit\Email;

use Give\Donations\Models\Donation;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_New_Offline_Donation_Email;
use Give_Offline_Donation_Instruction_Email;

/**
 * @since TBD
 */
class OfflineDonationEmailListenersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testNonOfflineDonationBailsBeforeLoadingTheLegacyPayment()
    {
        global $wpdb;

        $donation = Donation::factory()->create(['gatewayId' => 'manual']);
        $attempts = 0;
        add_filter('give_is_stop_email_notification', static function () use (&$attempts) {
            $attempts++;
            return true;
        });

        $before = $wpdb->num_queries;
        Give_New_Offline_Donation_Email::get_instance()->setup_email_notification($donation->id);
        Give_Offline_Donation_Instruction_Email::get_instance()->setup_email_notification($donation->id);

        $this->assertSame(0, $attempts, 'no send attempted for a non-offline donation');
        $this->assertLessThanOrEqual(2, $wpdb->num_queries - $before, 'one meta lookup per listener at most, no Give_Payment');
    }

    /**
     * @since TBD
     */
    public function testOfflineDonationStillReachesSend()
    {
        $donation = Donation::factory()->create(['gatewayId' => 'offline']);
        $attempts = 0;
        add_filter('give_is_stop_email_notification', static function () use (&$attempts) {
            $attempts++;
            return true;
        });

        Give_New_Offline_Donation_Email::get_instance()->setup_email_notification($donation->id);
        Give_Offline_Donation_Instruction_Email::get_instance()->setup_email_notification($donation->id);

        $this->assertSame(2, $attempts);
    }
}
