<?php

namespace Give\DonationForms\Listeners;

use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\FieldsAPI\Amount;
use Give\Framework\FieldsAPI\Exceptions\NameCollisionException;

class UpdateDonationLevelId
{
    /**
     * if the intended donation amount matches a donation level from the amount block settings,
     * this will update the donation level ID meta with the level array key,
     * which is used in the donation details screen.
     *
     * @since 3.0.0
     *
     * @throws NameCollisionException|Exception
     */
    public function __invoke(DonationForm $donationForm, Donation $donation)
    {
        /** @var Amount $amountField */
        $amountField = $donationForm->schema()->getNodeByName('amount');

        if (!$amountField) {
            return;
        }

        $levels = $amountField->getLevels();

        if ($donation->levelId !== '') {
            $currentLevelId = (int)$donation->levelId;
            if (isset($levels[$currentLevelId]) && (float)$levels[$currentLevelId]['value'] === (float)$donation->intendedAmount()->formatToDecimal()) {
                return;
            }
        }

        $levelValues = array_map(static function ($level) {
            return isset($level['value']) ? (float)$level['value'] : null;
        }, $levels);

        $donationLevel = array_search(
            (float)$donation->intendedAmount()->formatToDecimal(),
            $levelValues,
            true
        );

        if ($donationLevel !== false) {
            $donation->levelId = (string)$donationLevel;
            $donation->save();
        }
    }
}
