<?php

namespace Give\Tests\Unit\DonationForms\TestTraits;

use Give\DonationForms\V2\Models\DonationForm;
use Give\DonationForms\V2\Properties\DonationFormLevel;
use Give\DonationForms\V2\ValueObjects\DonationFormStatus;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Framework\Support\ValueObjects\Money;
use Give_Donate_Form;
use Give_Helper_Form;

trait LegacyDonationFormAdapter
{
    /**
     * @since 3.4.0 added $args parameter
     * @since 2.25.0
     */
    public function createSimpleDonationForm(array $args = []): DonationForm
    {
        return $this->getDonationFormModelFromLegacyGiveDonateForm(Give_Helper_Form::create_simple_form($args));
    }

    /**
     * @since 2.25.0
     */
    public function createMultiLevelDonationForm(array $args = []): DonationForm
    {
        return $this->getDonationFormModelFromLegacyGiveDonateForm(Give_Helper_Form::create_multilevel_form($args));
    }

    /**
     * @since 2.25.0
     */
    public function getDonationFormModelFromLegacyGiveDonateForm(Give_Donate_Form $giveDonateForm): DonationForm
    {
        // assign the donation levels
        $levels = [];

        if ($giveDonateForm->is_multi_type_donation_form()) {
            foreach ($giveDonateForm->get_prices() as $mockFormLevel) {
                $levels[] = DonationFormLevel::fromArray($mockFormLevel);
            }
        } else {
            $levels[] = DonationFormLevel::fromPrice($giveDonateForm->get_price());
        }

        return new DonationForm([
            'id' => $giveDonateForm->get_ID(),
            'title' => $giveDonateForm->post_title,
            'goalOption' => $giveDonateForm->has_goal(),
            'totalNumberOfDonations' => (int)$giveDonateForm->get_sales(),
            'totalAmountDonated' => Money::fromDecimal($giveDonateForm->get_earnings(), give_get_currency()),
            'createdAt' => Temporal::toDateTime($giveDonateForm->post_date),
            'updatedAt' => Temporal::toDateTime($giveDonateForm->post_date),
            'status' => new DonationFormStatus($giveDonateForm->post_status),
            'levels' => $levels
        ]);
    }

}
