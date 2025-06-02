<?php

namespace Give\Subscriptions\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\BatchMigration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * Class BackfillMissingCampaignIdForSubscriptionRenewals
 *
 * This migration backfills missing campaignId for existing subscription renewal donations.
 * It ensures that renewal donations inherit the campaignId from their parent payment,
 * or discovers it through form-campaign associations when the parent has no direct campaignId.
 *
 * @unreleased
 */
class BackfillMissingCampaignIdForSubscriptionRenewals extends BatchMigration
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'subscriptions_backfill_missing_campaign_id_for_legacy_subscription_renewals';
    }

    /**
     * @inheritDoc
     */
    public static function title(): string
    {
        return 'Backfill missing campaignId for legacy subscription renewals';
    }

    /**
     * @inheritDoc
     */
    public static function timestamp(): string
    {
        return strtotime('2025-06-02 00:00:00');
    }

    /**
     * Base query to find subscription renewal donations without campaignId
     *
     * @unreleased
     */
    protected function query(): QueryBuilder
    {
        return DB::table('posts', 'donations')
            ->where('donations.post_type', 'give_payment')
            ->where('donations.post_status', 'give_subscription') // Renewal status
            ->whereNotExists(function (QueryBuilder $builder) {
                $builder
                    ->select('meta_id')
                    ->from('give_donationmeta')
                    ->where('meta_key', '_give_campaign_id')
                    ->whereRaw('AND donation_id = donations.ID');
            });
    }

    /**
     * @inheritDoc
     * @throws DatabaseMigrationException
     */
    public function runBatch($firstId, $lastId)
    {
        try {
            $query = $this->query()->select('donations.ID');

            // Migration Runner will pass null for lastId in the last step
            if (is_null($lastId)) {
                $query->where('donations.ID', $firstId, '>');
            } else {
                $query->whereBetween('donations.ID', $firstId, $lastId);
            }

            $renewalDonations = $query->getAll();

            foreach ($renewalDonations as $renewalDonationRow) {
                $this->processRenewalDonation($renewalDonationRow->ID);
            }
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException('An error occurred while backfilling missing campaignId for subscription renewals', 0, $exception);
        }
    }

    /**
     * Process a single renewal donation to assign missing campaignId
     *
     * @unreleased
     */
    private function processRenewalDonation(int $renewalDonationId): void
    {
        $campaignId = null;

        // Find the parent payment ID for this renewal
        $parentPaymentId = $this->getParentPaymentId($renewalDonationId);

        // First, try to get the campaignId from the parent donation (if parent exists)
        if ($parentPaymentId) {
            $parentCampaignId = DB::table('give_donationmeta')
                ->where('donation_id', $parentPaymentId)
                ->where('meta_key', '_give_campaign_id')
                ->value('meta_value');

            if (!empty($parentCampaignId)) {
                $campaignId = (int)$parentCampaignId;
            }
        }

        // If no campaignId found yet, try to find the campaign by form ID
        if (!$campaignId) {
            // Try to get form ID from parent payment first, then from renewal donation itself
            $formId = null;

            if ($parentPaymentId) {
                $formId = DB::table('give_donationmeta')
                    ->where('donation_id', $parentPaymentId)
                    ->where('meta_key', '_give_payment_form_id')
                    ->value('meta_value');
            }

            // If no form ID from parent, try to get it from the renewal donation itself
            if (!$formId) {
                $formId = DB::table('give_donationmeta')
                    ->where('donation_id', $renewalDonationId)
                    ->where('meta_key', '_give_payment_form_id')
                    ->value('meta_value');
            }

            if ($formId) {
                // Find campaign associated with this form
                $campaignFromForm = DB::table('give_campaign_forms')
                    ->where('form_id', $formId)
                    ->value('campaign_id');

                if ($campaignFromForm) {
                    $campaignId = (int)$campaignFromForm;
                }
            }
        }

        if ($campaignId) {
            // Use WordPress/GiveWP meta function instead of raw DB insert
            give()->payment_meta->update_meta($renewalDonationId, '_give_campaign_id', $campaignId);
        }
    }

    /**
     * Get the parent payment ID for a renewal donation
     *
     * @unreleased
     */
    private function getParentPaymentId(int $renewalDonationId): ?int
    {
        // Get subscription ID from the renewal donation
        $subscriptionId = DB::table('give_donationmeta')
            ->where('donation_id', $renewalDonationId)
            ->where('meta_key', '_give_subscription_id')
            ->value('meta_value');

        if (!$subscriptionId) {
            return null;
        }

        // Get parent payment ID from the subscription
        $parentPaymentId = DB::table('give_subscriptions')
            ->where('id', $subscriptionId)
            ->value('parent_payment_id');

        return $parentPaymentId ? (int)$parentPaymentId : null;
    }

    /**
     * @inheritDoc
     */
    public function getItemsCount(): int
    {
        return $this->query()->count();
    }

    /**
     * @inheritDoc
     */
    public function getBatchItemsAfter($lastId): ?array
    {
        $items = $this->query()
            ->select('donations.ID')
            ->where('donations.ID', $lastId, '>')
            ->orderBy('donations.ID')
            ->limit($this->getBatchSize())
            ->getAll();

        if (!$items) {
            return null;
        }

        return [
            min($items)->ID,
            max($items)->ID,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getBatchSize(): int
    {
        return 100;
    }

    /**
     * @inheritDoc
     */
    public function hasMoreItemsToBatch($lastProcessedId): ?bool
    {
        return $this->query()
            ->where('donations.ID', $lastProcessedId, '>')
            ->count() > 0;
    }
}
