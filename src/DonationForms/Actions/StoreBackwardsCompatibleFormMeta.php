<?php

namespace Give\DonationForms\Actions;

use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\V2\ValueObjects\DonationFormMetaKeys;
use Give\DonationForms\ValueObjects\GoalType;
use Give\Donations\ValueObjects\DonationType;
use Give\Framework\FieldsAPI\Amount;
use Give\Framework\FieldsAPI\DonationAmount;
use Give\Framework\FieldsAPI\Field;

class StoreBackwardsCompatibleFormMeta
{
    /**
     * @since 3.0.0
     */
    public function __invoke(DonationForm $donationForm)
    {
        $this->storeDonationLevels($donationForm);
        $this->storeDonationGoal($donationForm);
        $this->storeRecurringMetaKeys($donationForm);
    }

    /**
     * @since 3.12.0 Update to store donation levels with descriptions
     * @since 3.0.0 update with dynamic values from amount field
     * @since 3.0.0
     */
    public function storeDonationLevels(DonationForm $donationForm)
    {
        /** @var Amount $amountField */
        $amountField = $donationForm->schema()->getNodeByName('amount');

        if (!$amountField) {
            return;
        }

        $donationLevels = $amountField->getLevels();

        if ($donationLevels) {
            $donationLevels = array_map(static function ($donationLevel, $index) {
                return [
                    '_give_id' => [
                        'level_id' => $index,
                    ],
                    '_give_amount' => $donationLevel['value'],
                    '_give_text' => $donationLevel['label'] ?? '',
                ];
            }, $donationLevels, array_keys($donationLevels));

            $this->saveSingleFormMeta($donationForm->id, DonationFormMetaKeys::PRICE_OPTION, 'multi');
            $this->saveSingleFormMeta($donationForm->id, DonationFormMetaKeys::DONATION_LEVELS, $donationLevels);
        } else {
            $this->saveSingleFormMeta($donationForm->id, DonationFormMetaKeys::PRICE_OPTION, 'set');
            $this->saveSingleFormMeta(
                $donationForm->id,
                DonationFormMetaKeys::SET_PRICE,
                $amountField->getFixedAmountValue()
            );
            $this->saveSingleFormMeta($donationForm->id, DonationFormMetaKeys::DONATION_LEVELS, []);
        }
    }

    /**
     * @since 3.0.0
     */
    public function storeDonationGoal(DonationForm $donationForm)
    {
        $this->saveSingleFormMeta(
            $donationForm->id,
            DonationFormMetaKeys::GOAL_OPTION,
            $donationForm->settings->enableDonationGoal ? 'enabled' : 'disabled'
        );

        $goalType = $donationForm->settings->goalType;

        if (!$goalType) {
            return;
        }

        $legacyGoalType = $this->getLegacyGoalTypeValue($goalType);

        $this->saveSingleFormMeta($donationForm->id, '_give_goal_format', $legacyGoalType);

        $metaLookup = [
            'donation' => '_give_number_of_donation_goal',
            'donors' => '_give_number_of_donor_goal',
            'amount' => '_give_set_goal',
        ];

        $goalAmount = ('amount' === $legacyGoalType) ? give_sanitize_amount_for_db(
            $donationForm->settings->goalAmount
        ) : $donationForm->settings->goalAmount;

        $this->saveSingleFormMeta($donationForm->id, $metaLookup[$legacyGoalType], $goalAmount);

        if ($goalType->isOneOf(
            GoalType::SUBSCRIPTIONS(),
            GoalType::DONORS_FROM_SUBSCRIPTIONS(),
            GoalType::AMOUNT_FROM_SUBSCRIPTIONS()
        )) {
            $this->saveSingleFormMeta($donationForm->id, '_give_recurring_goal_format', 1);
        }
    }

    protected function getLegacyGoalTypeValue(GoalType $goalType): string
    {
        switch ($goalType):
            case GoalType::DONATIONS():
                // Legacy uses "donation" instead of "donations".
                return 'donation';
            case GoalType::DONORS():
                return 'donors';
            default:
                return 'amount';
        endswitch;
    }

    /**
     * @since 3.0.0
     */
    protected function saveSingleFormMeta($formId, $metaKey, $metaValue)
    {
        give()->form_meta->update_meta($formId, $metaKey, $metaValue);
    }

    /**
     * @since 3.0.0
     */
    protected function storeRecurringMetaKeys(DonationForm $donationForm)
    {
        $donationFormSchema = $donationForm->schema();

        /** @var Field|null $donationTypeField */
        $donationTypeField = $donationFormSchema->getNodeByName('donationType');
        $donationType = !$donationTypeField ? DonationType::SINGLE() : new DonationType(
            $donationTypeField->getDefaultValue()
        );

        if (!$donationType->isSubscription()) {
            $this->saveSingleFormMeta(
                $donationForm->id,
                '_give_recurring',
                'no'
            );

            return;
        }

        /** @var DonationAmount $donationAmountField */
        $donationAmountField = $donationFormSchema->getNodeByName('donationAmount');

        /** @var  Field $subscriptionPeriodField */
        $subscriptionPeriodField = $donationFormSchema->getNodeByName('subscriptionPeriod');

        /** @var  Field $subscriptionFrequencyField */
        $subscriptionFrequencyField = $donationFormSchema->getNodeByName('subscriptionFrequency');

        /** @var  Field $subscriptionInstallmentsField */
        $subscriptionInstallmentsField = $donationFormSchema->getNodeByName('subscriptionInstallments');

        $this->saveSingleFormMeta(
            $donationForm->id,
            '_give_recurring',
            $donationAmountField->subscriptionDetailsAreFixed ? 'yes_admin' : 'yes'
        );

        // period
        $this->saveSingleFormMeta(
            $donationForm->id,
            '_give_period',
            $subscriptionPeriodField->getDefaultValue()
        );

        // frequency
        $this->saveSingleFormMeta(
            $donationForm->id,
            '_give_period_interval',
            $subscriptionFrequencyField->getDefaultValue()
        );

        // times
        $this->saveSingleFormMeta(
            $donationForm->id,
            '_give_times',
            $subscriptionInstallmentsField->getDefaultValue()
        );
    }
}
