<?php

namespace Give\Revenue;

use Give\Revenue\Repositories\Revenue;
use Give\ValueObjects\Money;

/**
 * Class OnDonationHandler
 * @package Give\Revenue
 * @since 2.9.0
 *
 * use this class to insert revenue when new donation create.
 */
class DonationHandler
{
    /**
     * Handle new donation.
     *
     * @unreleased - set campaign id
     * @since 2.9.0
     *
     * @param int $donationId
     *
     */
    public function handle($donationId)
    {
        $amount = give_donation_amount($donationId);
        $currency = give_get_option('currency');
        $formId = give_get_payment_form_id($donationId);
        $campaign = give()->campaigns->getByFormId($formId);

        $data = [
            'donation_id' => $donationId,
            'form_id' =>  $formId,
            'amount' => Money::of($amount, $currency)->getMinorAmount(),
            'campaign_id' => $campaign ? $campaign->id : 0,
        ];

        give(Revenue::class)->insert($data);
    }
}
