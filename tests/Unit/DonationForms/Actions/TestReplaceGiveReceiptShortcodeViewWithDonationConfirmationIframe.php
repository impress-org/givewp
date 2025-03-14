<?php

namespace Give\Tests\Unit\DonationForms\Actions;

use Give\DonationForms\Actions\GenerateDonationConfirmationReceiptViewRouteUrl;
use Give\DonationForms\Actions\ReplaceGiveReceiptShortcodeViewWithDonationConfirmationIframe;
use Give\Donations\Models\Donation;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 3.16.0
 */
class TestReplaceGiveReceiptShortcodeViewWithDonationConfirmationIframe extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.16.0
     */
    public function testShouldNotReplaceGiveReceiptShortcodeViewWithDonationConfirmationIframe()
    {
        $view = 'originalView';

        $result = (new ReplaceGiveReceiptShortcodeViewWithDonationConfirmationIframe())($view);

        $this->assertEquals($view, $result);
    }

    /**
     * @since 3.16.0
     */
    public function testShouldNotReplaceGiveReceiptShortcodeViewWithDonationConfirmationIframeIfInvalidReceiptId(): void
    {
        $view = 'originalView';
        $_GET['receipt_id'] = 1234;

        $result = (new ReplaceGiveReceiptShortcodeViewWithDonationConfirmationIframe())($view);

        $this->assertEquals($view, $result);
    }

    /**
     * @since 3.16.0
     */
    public function testShouldReplaceGiveReceiptShortcodeViewWithDonationConfirmationIframe(): void
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create();
        $view = 'originalView';
        $receiptId = $donation->purchaseKey;
        $_GET['receipt-id'] = $receiptId;

        $result = (new ReplaceGiveReceiptShortcodeViewWithDonationConfirmationIframe())($view);
        $replacedViewUrl = (new GenerateDonationConfirmationReceiptViewRouteUrl())($receiptId);

        $this->assertEquals("<iframe style='width: 1px;min-width: 100%;border: 0;' data-givewp-embed src='$replacedViewUrl'></iframe>", $result);
    }
}
