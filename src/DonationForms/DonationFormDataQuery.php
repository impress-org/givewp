<?php

namespace Give\DonationForms;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * Class used for loading the number of donors, donations and revenue amounts for multiple forms
 *
 * @since 4.3.0
 */
class DonationFormDataQuery extends QueryBuilder
{
    /**
     * @since 4.3.0
     */
    private function __construct()
    {
        $this->select(['formId.meta_value', 'form_id']);
        $this->whereIn('donation.post_status', ['publish', 'give_subscription']);
        $this->groupBy('form_id');
    }


    /**
     * Donations query for campaigns
     *
     * @param int[] $formIds
     */
    public static function donations(array $formIds): DonationFormDataQuery
    {
        return (new self())
            ->from('posts', 'donation')
            ->where('post_type', 'give_payment')
            ->joinDonationMeta(DonationMetaKeys::FORM_ID, 'formId')
            ->joinDonationMeta(DonationMetaKeys::MODE, 'paymentMode')
            ->whereIn('formId.meta_value', $formIds)
            ->where('paymentMode.meta_value', give_is_test_mode() ? 'test' : 'live');
    }

    /**
     * Subscriptions query for forms
     *
     * @since 4.3.0
     *
     * @param int[] $formIds
     */
    public static function subscriptions(array $formIds): DonationFormDataQuery
    {
        return (new self())
            ->from('give_subscriptions', 'subscription')
            ->join(function (JoinQueryBuilder $builder) {
                $builder
                    ->leftJoin('posts', 'donation')
                    ->on('subscription.parent_payment_id', 'donation.ID');
            })
            ->joinDonationMeta(DonationMetaKeys::FORM_ID, 'formId')
            ->whereIn('formId.meta_value', $formIds)
            ->where('payment_mode', give_is_test_mode() ? 'test' : 'live')
            ->where('donation.post_type', 'give_payment');
    }

    /**
     * Returns a calculated sum of the intended amounts (without recovered fees) for the donations.
     *
     * @since 4.3.0
     */
    public function collectIntendedAmounts(): ?array
    {
        return (clone $this)
            ->select('SUM((IFNULL(amount.meta_value, 0) - IFNULL(feeAmountRecovered.meta_value, 0)) / IFNULL(exchangeRate.meta_value, 1)) as sum')
            ->joinDonationMeta(DonationMetaKeys::AMOUNT, 'amount')
            ->joinDonationMeta(DonationMetaKeys::FEE_AMOUNT_RECOVERED, 'feeAmountRecovered')
            ->joinDonationMeta(DonationMetaKeys::EXCHANGE_RATE, 'exchangeRate')
            ->getAll(ARRAY_A);
    }

    /**
     * Returns a calculated sum of the initial amounts
     *
     * @since 4.3.0
     */
    public function collectInitialAmounts(): ?array
    {
        return (clone $this)
            ->select('SUM(initial_amount) as sum')
            ->getAll(ARRAY_A);
    }

    /**
     * @since 4.3.0
     */
    public function collectDonations(): ?array
    {
        return (clone $this)
            ->select('COUNT(donation.ID) as count')
            ->getAll(ARRAY_A);
    }

    /**
     * @since 4.3.0
     */
    public function collectDonors(): ?array
    {
        return (clone $this)
            ->select('COUNT(DISTINCT donorId.meta_value) as count')
            ->joinDonationMeta(DonationMetaKeys::DONOR_ID, 'donorId')
            ->getAll(ARRAY_A);
    }

    /**
     * @since 4.3.0
     */
    private function joinDonationMeta($key, $alias): self
    {
        $this->join(function (JoinQueryBuilder $builder) use ($key, $alias) {
            $builder
                ->leftJoin('give_donationmeta', $alias)
                ->on('donation.ID', $alias . '.donation_id')
                ->andOn($alias . '.meta_key', $key, true);
        });

        return $this;
    }
}
