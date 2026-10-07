<?php

namespace Give\Tests\Unit\LegacyPayments;

use Give\Campaigns\Models\Campaign;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Payment;
use Give_Payment_History_Table;

/**
 * @since TBD
 */
final class PaymentHistoryTableCampaignColumnTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
        require_once GIVE_PLUGIN_DIR . 'includes/admin/payments/class-payments-table.php';
    }

    /**
     * @since TBD
     */
    public function testSaysNoCampaignForADonationToAStandaloneForm(): void
    {
        $form = DonationForm::factory()->create();
        $donation = Donation::factory()->create(['formId' => $form->id, 'campaignId' => null]);

        $this->assertSame('No campaign', $this->renderCampaignCell($donation));
    }

    /**
     * @since TBD
     */
    public function testLinksToTheCampaignOfADonationThatHasOne(): void
    {
        $campaign = Campaign::factory()->create(['title' => 'Spring Appeal']);
        $donation = Donation::factory()->create([
            'formId' => $campaign->defaultFormId,
            'campaignId' => $campaign->id,
        ]);

        $cell = $this->renderCampaignCell($donation);

        $this->assertStringContainsString('Spring Appeal', $cell);
        $this->assertStringContainsString("id={$campaign->id}&", $cell);
    }

    /**
     * @since TBD
     */
    private function renderCampaignCell(Donation $donation): string
    {
        return (new Give_Payment_History_Table())->column_default(new Give_Payment($donation->id), 'campaign');
    }
}
