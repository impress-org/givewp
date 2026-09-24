<?php

namespace Give\DonationForms\Repositories;


use Give\Campaigns\CampaignsDataQuery;
use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\DonationForms\DonationFormDataQuery;
use Give\DonationForms\V2\Models\DonationForm;
use Give\DonationForms\ValueObjects\GoalSource;
use Give\DonationForms\ValueObjects\GoalType;
use Give\Framework\Database\DB;

/**
 * @since 4.3.0
 *
 * Used to optimize the donation forms list table performance and to avoid n+1 problems.
 */
class DonationFormDataRepository
{
    private array $formAmounts = [];
    private array $campaignAmounts = [];
    private array $formSubscriptionAmounts = [];
    private array $campaignSubscriptionAmounts = [];
    private array $formDonationsCount = [];
    private array $campaignDonationsCount = [];
    private array $formSubscriptionDonationsCount = [];
    private array $campaignSubscriptionDonationsCount = [];
    private array $formDonorsCount = [];
    private array $campaignDonorsCount = [];
    private array $formSubscriptionDonorsCount = [];
    private array $campaignSubscriptionDonorsCount = [];

    /**
     * @since TBD
     *
     * @var array<int, object{id: int, title: string, defaultFormId: int, type: string}> keyed by form id
     */
    private array $campaigns = [];

    /**
     * @param DonationForm[] $forms
     *
     * @return DonationFormDataRepository
     */
    public static function forms(array $forms): DonationFormDataRepository
    {
        $self = new self();

        $formsUsingFormGoal = [];
        $formsUsingCampaignGoal = [];

        $self->setFormsCampaigns(array_map(static function ($form) {
            return $form->id;
        }, $forms));

        foreach ($forms as $form) {
            if ($form->goalSettings->goalSource === GoalSource::CAMPAIGN) {
                $formsUsingCampaignGoal[] = $form->id;
            } else {
                $formsUsingFormGoal[] = $form->id;
            }
        }

        if ( ! empty($formsUsingFormGoal)) {
            $self->setFormsGoals($formsUsingFormGoal);
        }

        if ( ! empty($formsUsingCampaignGoal)) {
            $self->setFormsCampaignGoals($formsUsingCampaignGoal);
        }

        return $self;
    }


    /**
     * @since 4.3.0
     */
    public function setFormsGoals(array $ids): void
    {
        $donations = DonationFormDataQuery::donations($ids);

        $this->formAmounts = $donations->collectIntendedAmounts();
        $this->formDonationsCount = $donations->collectDonations();
        $this->formDonorsCount = $donations->collectDonors();

        // Set subscriptions data
        if (defined('GIVE_RECURRING_VERSION')) {
            $subscriptions = DonationFormDataQuery::subscriptions($ids);

            $this->formSubscriptionAmounts = $subscriptions->collectInitialAmounts();
            $this->formSubscriptionDonationsCount = $subscriptions->collectDonations();
            $this->formSubscriptionDonorsCount = $subscriptions->collectDonors();
        }
    }

    /**
     * Load the campaign each form belongs to. Forms attached through the campaign forms table
     * come first; the campaign's own default form id covers Peer-to-Peer forms that have no row there.
     *
     * @since TBD
     */
    public function setFormsCampaigns(array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        $linked = DB::table('give_campaign_forms', 'campaign_forms')
            ->select(
                ['campaign_forms.form_id', 'formId'],
                ['campaigns.id', 'id'],
                ['campaigns.campaign_title', 'title'],
                ['campaigns.form_id', 'defaultFormId'],
                ['campaigns.campaign_type', 'type']
            )
            ->leftJoin('give_campaigns', 'campaign_forms.campaign_id', 'campaigns.id', 'campaigns')
            ->whereIn('campaign_forms.form_id', $ids)
            ->getAll();

        $defaults = DB::table('give_campaigns')
            ->select(
                ['form_id', 'formId'],
                ['id', 'id'],
                ['campaign_title', 'title'],
                ['form_id', 'defaultFormId'],
                ['campaign_type', 'type']
            )
            ->whereIn('form_id', $ids)
            ->getAll();

        foreach (array_merge($linked ?? [], $defaults ?? []) as $row) {
            if ($row->id && ! isset($this->campaigns[(int)$row->formId])) {
                $this->campaigns[(int)$row->formId] = (object)[
                    'id' => (int)$row->id,
                    'title' => $row->title,
                    'defaultFormId' => (int)$row->defaultFormId,
                    'type' => (string)$row->type,
                ];
            }
        }
    }

    /**
     * @since TBD
     *
     * @return object{id: int, title: string, defaultFormId: int, type: string}|null
     */
    public function getCampaign(DonationForm $form)
    {
        return $this->campaigns[$form->id] ?? null;
    }

    /**
     * @since 4.3.0
     */
    public function setFormsCampaignGoals(array $ids): void
    {
        $campaigns = DB::table('give_campaign_forms')
            ->distinct()
            ->select(['campaign_id', 'id'])
            ->whereIn('form_id', $ids)
            ->getAll();

        $ids = array_map(function ($campaign) {
            return (int)$campaign->id;
        }, $campaigns);

        $donations = CampaignsDataQuery::donations($ids);

        $this->campaignAmounts = $donations->collectIntendedAmounts();
        $this->campaignDonationsCount = $donations->collectDonations();
        $this->campaignDonorsCount = $donations->collectDonors();

        // Set subscriptions data
        if (defined('GIVE_RECURRING_VERSION')) {
            $subscriptions = CampaignsDataQuery::subscriptions($ids);

            $this->campaignSubscriptionAmounts = $subscriptions->collectInitialAmounts();
            $this->campaignSubscriptionDonationsCount = $subscriptions->collectDonations();
            $this->campaignSubscriptionDonorsCount = $subscriptions->collectDonors();
        }
    }

    /**
     * @since 4.3.0
     *
     * Get revenue for form
     *
     * @param DonationForm $form
     *
     * @return int|float
     */
    public function getRevenue(DonationForm $form)
    {
        $data = $form->goalSettings->goalType->isAmountFromSubscriptions()
            ? $this->{$form->goalSettings->goalSource . 'SubscriptionAmounts'}
            : $this->{$form->goalSettings->goalSource . 'Amounts'};

        $source = $this->getSourceData($form);

        foreach ($data as $row) {
            if (isset($row[$source['column']]) && $row[$source['column']] == $source['id']) {
                return $row['sum'];
            }
        }

        return 0;
    }

    /**
     * @since 4.3.0
     *
     * Get donations count for form
     *
     * @param DonationForm $form
     *
     * @return int
     */
    public function getDonationsCount(DonationForm $form): int
    {
        $data = $form->goalSettings->goalType->isSubscriptions()
            ? $this->{$form->goalSettings->goalSource . 'SubscriptionDonationsCount'}
            : $this->{$form->goalSettings->goalSource . 'DonationsCount'};

        $source = $this->getSourceData($form);

        foreach ($data as $row) {
            if (isset($row[$source['column']]) && $row[$source['column']] == $source['id']) {
                return (int)$row['count'];
            }
        }

        return 0;
    }

    /**
     * @since 4.3.0
     *
     * Get donors count for form
     *
     * @param DonationForm $form
     *
     * @return int
     */
    public function getDonorsCount(DonationForm $form): int
    {
        $data = $form->goalSettings->goalType->isSubscriptions()
            ? $this->{$form->goalSettings->goalSource . 'SubscriptionDonorsCount'}
            : $this->{$form->goalSettings->goalSource . 'DonorsCount'};

        $source = $this->getSourceData($form);

        foreach ($data as $row) {
            if (isset($row[$source['column']]) && $row[$source['column']] == $source['id']) {
                return (int)$row['count'];
            }
        }

        return 0;
    }


    /**
     * @since 4.3.0
     *
     * Get goal data for form
     *
     * @param DonationForm $form
     *
     * @return array{actual: int, goal: int, actualFormatted: string, goalFormatted:string, percentage:float}
     */
    public function getGoalData(DonationForm $form): array
    {
        $actual = $this->getActualGoal($form);
        $percentage = $form->goalSettings->goalAmount
            ? $actual / $form->goalSettings->goalAmount
            : 0;

        $typeIsMoney = $form->goalSettings->goalType->isOneOf(
            GoalType::AMOUNT(),
            GoalType::AMOUNT_FROM_SUBSCRIPTIONS(),
        );

        return [
            'actual' => $actual,
            'goal' => $form->goalSettings->goalAmount,
            'actualFormatted' => $typeIsMoney
                ? give_currency_filter(give_format_amount($actual))
                : $actual,
            'goalFormatted' => $typeIsMoney
                ? give_currency_filter(give_format_amount($form->goalSettings->goalAmount))
                : $form->goalSettings->goalAmount,
            'percentage' => round($percentage * 100, 2),
            'typeIsMoney' => $typeIsMoney,
        ];
    }

    /**
     * @since 4.3.0
     *
     * @param DonationForm $form
     *
     * @return int|float
     */
    private function getActualGoal(DonationForm $form)
    {
        switch ($form->goalSettings->goalType->getValue()) {
            case GoalType::DONATIONS():
            case GoalType::SUBSCRIPTIONS():
                return $this->getDonationsCount($form);
            case GoalType::DONORS():
            case GoalType::DONORS_FROM_SUBSCRIPTIONS():
                return $this->getDonorsCount($form);
            default:
                return $this->getRevenue($form);
        }
    }

    /**
     * @param DonationForm $form
     *
     * @return array{id: int, column: string}
     */
    private function getSourceData(DonationForm $form): array
    {
        if ($form->goalSettings->goalSource === GoalSource::CAMPAIGN) {
            return [
                'id' => $form->campaignId,
                'column' => 'campaign_id',
            ];
        }

        return [
            'id' => $form->id,
            'column' => 'form_id',
        ];
    }
}
