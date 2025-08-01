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
 * @since 4.3.0 add GoalSettings
 * @since      2.24.0
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
     * @since 4.3.0
     */
    public GoalSettings $goalSettings;

    /**
     * @since 4.3.0
     */
    public bool $usesFormBuilder;

    /**
     * @since 4.3.0
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
     * @since 4.6.0 Cast $queryObject->goalFormat to string
     * @since 4.3.0
     */
    private function getGoalSettings(object $queryObject): GoalSettings
    {
        $formSettings = $queryObject->{DonationFormMetaKeys::SETTINGS()->getKeyAsCamelCase()};

        // v3 form
        if ($formSettings) {
            $settings = FormSettings::fromjson($formSettings);
            // uses campaign goal settings
            if ($settings->goalSource->isCampaign()) {
                $campaign = Campaign::findByFormId($queryObject->id);
                $this->campaignId = $campaign->id;

                return GoalSettings::fromArray([
                    'goalSource' => $settings->goalSource->getValue(),
                    'enableDonationGoal' => $settings->enableDonationGoal,
                    'goalType' => $this->convertGoalType($campaign->goalType->getValue()),
                    'goalAmount' => $campaign->goal,
                ]);
            }

            return GoalSettings::fromArray([
                'goalSource' => $settings->goalSource->getValue(),
                'enableDonationGoal' => $settings->enableDonationGoal,
                'goalType' => $settings->goalType,
                'goalAmount' => $settings->goalAmount,
            ]);
        }

        // v2 form
        return GoalSettings::fromArray([
            'goalSource' => 'form',
            'enableDonationGoal' => $queryObject->goalOption === 'enabled',
            'goalType' => $this->convertGoalType((string)$queryObject->goalFormat, (bool)$queryObject->recurringGoalFormat),
            'goalAmount' => $queryObject->goalAmount,
        ]);
    }


    /**
     * @since 4.3.0
     */
    public function convertGoalType(string $type, bool $isRecurring = false): GoalType
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
            case 'subscriptions':
                return GoalType::SUBSCRIPTIONS();
            case 'donorsFromSubscriptions':
                return GoalType::DONORS_FROM_SUBSCRIPTIONS();
            case 'amountFromSubscriptions':
                return GoalType::AMOUNT_FROM_SUBSCRIPTIONS();
            default:
                return $isRecurring
                    ? GoalType::AMOUNT_FROM_SUBSCRIPTIONS()
                    : GoalType::AMOUNT();
        }
    }
}
