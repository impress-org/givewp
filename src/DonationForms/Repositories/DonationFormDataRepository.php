<?php

namespace Give\DonationForms\Repositories;


use Give\DonationForms\DonationFormDataQuery;
use Give\DonationForms\V2\Models\DonationForm;
use Give\DonationForms\ValueObjects\GoalType;

/**
 * @unreleased
 *
 * Used to optimize the donation forms list table performance and to avoid n+1 problems.
 */
class DonationFormDataRepository
{
    private array $amounts;
    private array $subscriptionAmounts = [];
    private array $donationsCount;
    private array $subscriptionDonationsCount = [];
    private array $donorsCount;
    private array $subscriptionDonorsCount = [];

    /**
     * @param int[] $ids
     *
     * @return DonationFormDataRepository
     */
    public static function forms(array $ids): DonationFormDataRepository
    {
        $self = new self();

        $donations = DonationFormDataQuery::donations($ids);

        $self->amounts = $donations->collectIntendedAmounts();
        $self->donationsCount = $donations->collectDonations();
        $self->donorsCount = $donations->collectDonors();

        // Set subscriptions data
        if (defined('GIVE_RECURRING_VERSION')) {
            $subscriptions = DonationFormDataQuery::subscriptions($ids);

            $self->subscriptionAmounts = $subscriptions->collectInitialAmounts();
            $self->subscriptionDonationsCount = $subscriptions->collectDonations();
            $self->subscriptionDonorsCount = $subscriptions->collectDonors();
        }

        return $self;
    }

    /**
     * @unreleased
     *
     * Get revenue for form
     *
     * @param DonationForm $form
     *
     * @return int|float
     */
    public function getRevenue(DonationForm $form)
    {
        $data = $form->goalSettings->goalType->isSubscriptions()
            ? $this->subscriptionAmounts
            : $this->amounts;

        foreach ($data as $row) {
            if (isset($row['form_id']) && $row['form_id'] == $form->id) {
                return (int)$row['sum'];
            }
        }

        return 0;
    }

    /**
     * @unreleased
     *
     * Get donations count for form
     *
     * @param DonationForm $form
     *
     * @return int|float
     */
    public function getDonationsCount(DonationForm $form)
    {
        $data = $form->goalSettings->goalType->isSubscriptions()
            ? $this->subscriptionDonationsCount
            : $this->donationsCount;

        foreach ($data as $row) {
            if (isset($row['form_id']) && $row['form_id'] == $form->id) {
                return (int)$row['count'];
            }
        }

        return 0;
    }

    /**
     * @unreleased
     *
     * Get donors count for form
     *
     * @param DonationForm $form
     *
     * @return int|float
     */
    public function getDonorsCount(DonationForm $form)
    {
        $data = $form->goalSettings->goalType->isSubscriptions()
            ? $this->subscriptionDonorsCount
            : $this->donorsCount;

        foreach ($data as $row) {
            if (isset($row['form_id']) && $row['form_id'] == $form->id) {
                return (int)$row['count'];
            }
        }

        return 0;
    }


    /**
     * @unreleased
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

        return [
            'actual' => $actual,
            'goal' => $form->goalSettings->goalAmount,
            'actualFormatted' => $form->goalSettings->goalType == GoalType::AMOUNT
                ? give_currency_filter(give_format_amount($actual))
                : $actual,
            'goalFormatted' => $form->goalSettings->goalType == GoalType::AMOUNT
                ? give_currency_filter(give_format_amount($form->goalSettings->goalAmount))
                : $form->goalSettings->goalAmount,
            'percentage' => round($percentage * 100, 2),
            'typeIsMoney' => $form->goalSettings->goalType->isOneOf(GoalType::AMOUNT(), GoalType::AMOUNT_FROM_SUBSCRIPTIONS())
        ];
    }

    /**
     * @unreleased
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
}
