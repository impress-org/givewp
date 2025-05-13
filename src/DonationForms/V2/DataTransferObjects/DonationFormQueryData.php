<?php

namespace Give\DonationForms\V2\DataTransferObjects;

use DateTime;
use Give\Campaigns\Models\Campaign;
use Give\DonationForms\Properties\FormSettings;
use Give\DonationForms\Properties\GoalSettings;
use Give\DonationForms\V2\Models\DonationForm;
use Give\DonationForms\V2\Properties\DonationFormLevel;
use Give\DonationForms\V2\ValueObjects\DonationFormMetaKeys as LegacyDonationFormMetaKeys;
use Give\DonationForms\V2\ValueObjects\DonationFormStatus;
use Give\DonationForms\ValueObjects\DonationFormMetaKeys;
use Give\DonationForms\ValueObjects\GoalType;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Framework\Support\ValueObjects\Money;

/**
 * Class DonationFormQueryData
 *
 * @unreleased add GoalSettings
 * @since 2.24.0
 */
final class DonationFormQueryData
{

    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $title;
    /**
     * @var DonationFormLevel[]
     */
    public $levels;
    /**
     * @var boolean
     */
    public $goalOption;
    /**
     * @var int
     */
    public $totalNumberOfDonations;
    /**
     * @var Money
     */
    public $totalAmountDonated;
    /**
     * @var DateTime
     */
    public $createdAt;
    /**
     * @var DateTime
     */
    public $updatedAt;
    /**
     * @var string
     */
    public $status;

    /**
     * @unreleased
     */
    public GoalSettings $goalSettings;

    /**
     * @unreleased
     */
    public bool $usesFormBuilder;

    /**
     * @unreleased
     */
    public int $campaignId;

    /**
     * Convert data from donation form object to DonationForm Model
     *
     * @since 2.24.0
     *
     * @param $object
     *
     * @return DonationFormQueryData
     */
    public static function fromObject($object): DonationFormQueryData
    {
        $self = new DonationFormQueryData();

        $self->campaignId = 0;
        $self->id = (int)$object->id;
        $self->title = $object->title;
        $self->levels = $self->getDonationFormLevels($object);
        $self->goalOption = ($object->{LegacyDonationFormMetaKeys::GOAL_OPTION()->getKeyAsCamelCase()} === 'enabled');
        $self->createdAt = Temporal::toDateTime($object->createdAt);
        $self->updatedAt = Temporal::toDateTime($object->updatedAt);
        $self->totalAmountDonated = Money::fromDecimal($object->{LegacyDonationFormMetaKeys::FORM_EARNINGS()->getKeyAsCamelCase()},
            give_get_currency());
        $self->totalNumberOfDonations = (int)$object->{LegacyDonationFormMetaKeys::FORM_SALES()->getKeyAsCamelCase()};
        $self->status = new DonationFormStatus($object->status);
        $self->goalSettings = $self->getGoalSettings($object);
        $self->usesFormBuilder = (bool)$object->settings;

        return $self;
    }

    /**
     * Convert DTO to DonationForm
     *
     * @return DonationForm
     */
    public function toDonationForm(): DonationForm
    {
        $attributes = get_object_vars($this);

        return new DonationForm($attributes);
    }

    /**
     * @since 2.24.0
     *
     * @param $object
     *
     * @return DonationFormLevel[]
     */
    public function getDonationFormLevels($object): array
    {
        switch ($object->{LegacyDonationFormMetaKeys::PRICE_OPTION()->getKeyAsCamelCase()}) {
            case 'multi':
                $levels = maybe_unserialize($object->{LegacyDonationFormMetaKeys::DONATION_LEVELS()->getKeyAsCamelCase()});

                if (empty($levels)) {
                    return [];
                }

                return array_map(static function ($level) {
                    return DonationFormLevel::fromArray($level);
                }, $levels);
            case 'set':
                $amount = $object->{LegacyDonationFormMetaKeys::SET_PRICE()->getKeyAsCamelCase()};

                if (empty($amount)) {
                    return [];
                }

                return [
                    DonationFormLevel::fromPrice($amount),
                ];
            default:
                return [];
        }
    }


    /**
     * @unreleased
     */
    private function getGoalSettings(object $queryObject): GoalSettings
    {
        $currency = give_get_option('currency', 'USD');
        $formSettings = $queryObject->{DonationFormMetaKeys::SETTINGS()->getKeyAsCamelCase()};

        // v3 form
        if ($formSettings) {
            $settings = FormSettings::fromjson($formSettings);
            // uses campaign goal settings
            if ($settings->goalSource->isCampaign()) {
                $campaign = Campaign::findByFormId($queryObject->id);
                $this->campaignId = $campaign->id;

                $goalType = $this->convertGoalType(
                    $campaign->goalType->getValue(),
                    (bool)$queryObject->recurringGoalFormat
                );

                return GoalSettings::fromArray([
                    'goalSource' => $settings->goalSource->getValue(),
                    'enableDonationGoal' => $settings->enableDonationGoal,
                    'goalType' => $goalType,
                    'goalAmount' => $goalType->isOneOf(GoalType::AMOUNT(), GoalType::AMOUNT_FROM_SUBSCRIPTIONS())
                        ? Money::fromDecimal($campaign->goal, $currency)->formatToDecimal()
                        : $campaign->goal,
                ]);
            }

            return GoalSettings::fromArray([
                'goalSource' => $settings->goalSource->getValue(),
                'enableDonationGoal' => $settings->enableDonationGoal,
                'goalType' => $settings->goalType,
                'goalAmount' => $settings->goalType->isOneOf(GoalType::AMOUNT(), GoalType::AMOUNT_FROM_SUBSCRIPTIONS())
                    ? Money::fromDecimal($queryObject->goalAmount, $currency)->formatToDecimal()
                    : $settings->goalAmount,
            ]);
        }


        // v2 form
        $goalType = $this->convertGoalType($queryObject->goalFormat, (bool)$queryObject->recurringGoalFormat);

        return GoalSettings::fromArray([
            'goalSource' => 'form',
            'enableDonationGoal' => $queryObject->goalOption === 'enabled',
            'goalType' => $goalType,
            'goalAmount' => $goalType->isOneOf(GoalType::AMOUNT(), GoalType::AMOUNT_FROM_SUBSCRIPTIONS())
                ? Money::fromDecimal($queryObject->goalAmount, $currency)->formatToDecimal()
                : $queryObject->goalAmount,
        ]);
    }


    /**
     * @unreleased
     */
    public function convertGoalType(string $type, bool $isRecurring): GoalType
    {
        switch ($type) {
            case 'donation':
            case 'donations':
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
