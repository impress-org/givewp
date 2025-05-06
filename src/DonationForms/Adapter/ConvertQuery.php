<?php

namespace Give\DonationForms\Adapter;

use Give\DonationForms\Properties\FormSettings;
use Give\DonationForms\V2\ValueObjects\DonationFormStatus;
use Give\DonationForms\ValueObjects\DonationFormMetaKeys;
use Give\DonationForms\ValueObjects\GoalType;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Framework\Support\ValueObjects\Money;

/**
 * @unreleased
 */
class ConvertQuery
{
    private string $currency;

    /**
     * @unreleased
     */
    public function __invoke(object $queryObject): Form
    {
        $this->currency = give_get_option('currency', 'USD');

        return new Form([
            'id' => (int)$queryObject->id,
            'title' => $queryObject->title,
            'createdAt' => Temporal::toDateTime($queryObject->createdAt),
            'updatedAt' => Temporal::toDateTime($queryObject->updatedAt),
            'status' => new DonationFormStatus($queryObject->status),
            'goalSettings' => $this->getGoalSettings($queryObject),
            'levels' => maybe_unserialize($queryObject->donationLevels),
            'usesFormBuilder' => (bool)$queryObject->settings,
        ]);
    }


    /**
     * @unreleased
     */
    private function getGoalSettings(object $queryObject): GoalSettings
    {
        $settings = $queryObject->{DonationFormMetaKeys::SETTINGS()->getKeyAsCamelCase()};

        if ($settings) {
            $settings = FormSettings::fromjson($settings);

            return GoalSettings::fromArray([
                'enableDonationGoal' => $settings->enableDonationGoal,
                'goalType' => $settings->goalType,
                'goalAmount' => $settings->goalType->isOneOf(GoalType::AMOUNT(), GoalType::AMOUNT_FROM_SUBSCRIPTIONS())
                    ? Money::fromDecimal($queryObject->goalAmount, $this->currency)->formatToDecimal()
                    : $settings->goalAmount,
            ]);
        }

        $goalType = $this->convertGoalType($queryObject->goalFormat, (bool)$queryObject->recurringGoalFormat);

        return GoalSettings::fromArray([
            'enableDonationGoal' => $queryObject->goalOption === 'enabled',
            'goalType' => $goalType,
            'goalAmount' => $goalType->isOneOf(GoalType::AMOUNT(), GoalType::AMOUNT_FROM_SUBSCRIPTIONS())
                ? Money::fromDecimal($queryObject->goalAmount, $this->currency)->formatToDecimal()
                : $settings->goalAmount,
        ]);
    }


    /**
     * @unreleased
     */
    public function convertGoalType(string $type, bool $isRecurring): GoalType
    {
        switch ($type) {
            case 'donation':
                return $isRecurring
                    ? GoalType::SUBSCRIPTIONS()
                    : GoalType::DONATIONS();
            case 'donors':
                return $isRecurring
                    ? GoalType::DONORS_FROM_SUBSCRIPTIONS()
                    : GoalType::DONORS();
            default:
                return $isRecurring
                    ? GoalType::AMOUNT_FROM_SUBSCRIPTIONS()
                    : GoalType::AMOUNT();
        }
    }
}
