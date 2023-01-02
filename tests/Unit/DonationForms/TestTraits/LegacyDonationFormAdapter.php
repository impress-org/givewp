<?php

namespace Give\Tests\Unit\DonationForms\TestTraits;

use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Properties\DonationFormLevel;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Framework\Support\ValueObjects\Money;
use Give_Donate_Form;
use Give_Helper_Form;

trait LegacyDonationFormAdapter
{
    /**
     * @unreleased
     */
    public function createSimpleDonationForm(): DonationForm
    {
        return $this->getDonationFormModelFromLegacyGiveDonateForm(Give_Helper_Form::create_simple_form());
    }

    /**
     * @unreleased
     */
    public function createMultiLevelDonationForm(): DonationForm
    {
        return $this->getDonationFormModelFromLegacyGiveDonateForm(Give_Helper_Form::create_multilevel_form());
    }

    /**
     * @unreleased
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
