<?php

namespace Give\Subscriptions\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\BatchMigration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * Class BackfillMissingCampaignIdForDonations
 *
 * This migration backfills missing campaignId for existing donations.
 * It ensures that donations have the campaignId from their parent payment (for renewals),
 * from the revenue table, or discovers it through form-campaign associations.
 *
 * @since 4.3.2
 */
class BackfillMissingCampaignIdForDonations extends BatchMigration
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'subscriptions_backfill_missing_campaign_id_for_donations';
    }

    /**
     * @inheritDoc
     */
    public static function title(): string
    {
        return 'Backfill missing campaignId for donations';
    }

    /**
     * @inheritDoc
     */
    public static function timestamp(): string
    {
        return strtotime('2025-06-03 00:00:00');
    }

    /**
     * Base query to find donations without campaignId
     *
     * @since 4.3.2
     */
    protected function query(): QueryBuilder
    {
        return DB::table('posts', 'donations')
            ->where('donations.post_type', 'give_payment')
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

            $donations = $query->getAll();
            $donationIds = array_column($donations, 'ID');

            if (empty($donationIds)) {
                return;
            }

            // Process donations in bulk
            $this->processDonationsBulk($donationIds);

        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException('An error occurred while backfilling missing campaignId for donations', 0, $exception);
        }
    }

    /**
     * Process multiple donations in bulk to assign missing campaignId
     *
     * @since 4.3.2
     */
    private function processDonationsBulk(array $donationIds): void
    {
        // Step 1: Get campaign IDs from revenue table for all donations
        $revenueCampaigns = $this->getBulkCampaignIdsFromRevenueTable($donationIds);

        // Step 2: Get subscription and parent payment data for remaining donations
        $subscriptionData = $this->getBulkSubscriptionData($donationIds);

        // Step 3: Get form-to-campaign mappings for remaining donations
        $formCampaigns = $this->getBulkFormCampaignMappings($donationIds, $revenueCampaigns, $subscriptionData);

        // Step 4: Prepare bulk insert data
        $metaInserts = [];

        foreach ($donationIds as $donationId) {
            $campaignId = null;

            // Priority 1: Revenue table
            if (isset($revenueCampaigns[$donationId])) {
                $campaignId = $revenueCampaigns[$donationId];
            }
            // Priority 2: Parent payment campaign ID
            elseif (isset($subscriptionData[$donationId]['parent_campaign_id'])) {
                $campaignId = $subscriptionData[$donationId]['parent_campaign_id'];
            }
            // Priority 3: Form-based campaign mapping
            elseif (isset($formCampaigns[$donationId])) {
                $campaignId = $formCampaigns[$donationId];
            }

            if ($campaignId) {
                $metaInserts[] = [
                    'donation_id' => $donationId,
                    'meta_key' => '_give_campaign_id',
                    'meta_value' => $campaignId
                ];
            }
        }

        // Step 5: Bulk insert meta data
        if (!empty($metaInserts)) {
            $this->bulkInsertMeta($metaInserts);
        }
    }

    /**
     * Get campaign IDs from revenue table for multiple donations
     *
     * @since 4.3.2
     */
    private function getBulkCampaignIdsFromRevenueTable(array $donationIds): array
    {
        $results = DB::table('give_revenue')
            ->select('donation_id', 'campaign_id')
            ->whereIn('donation_id', $donationIds)
            ->where('campaign_id', 0, '>')
            ->getAll();

        $campaigns = [];
        foreach ($results as $row) {
            $campaigns[$row->donation_id] = (int)$row->campaign_id;
        }

        return $campaigns;
    }

    /**
     * Get subscription and parent payment data for multiple donations
     *
     * @since 4.3.2
     */
    private function getBulkSubscriptionData(array $donationIds): array
    {
        // Get subscription IDs for all donations
        $subscriptionMeta = DB::table('give_donationmeta')
            ->select('donation_id', 'meta_value as subscription_id')
            ->whereIn('donation_id', $donationIds)
            ->where('meta_key', '_give_subscription_id')
            ->getAll();

        if (empty($subscriptionMeta)) {
            return [];
        }

        $subscriptionIds = array_column($subscriptionMeta, 'subscription_id');
        $donationToSubscription = [];
        foreach ($subscriptionMeta as $row) {
            $donationToSubscription[$row->donation_id] = $row->subscription_id;
        }

        // Get parent payment IDs from subscriptions
        $parentPayments = DB::table('give_subscriptions')
            ->select('id', 'parent_payment_id')
            ->whereIn('id', $subscriptionIds)
            ->where('parent_payment_id', 0, '>')
            ->getAll();

        if (empty($parentPayments)) {
            return [];
        }

        $subscriptionToParent = [];
        $parentPaymentIds = [];
        foreach ($parentPayments as $row) {
            $subscriptionToParent[$row->id] = $row->parent_payment_id;
            $parentPaymentIds[] = $row->parent_payment_id;
        }

        // Get campaign IDs from parent payments
        $parentCampaigns = DB::table('give_donationmeta')
            ->select('donation_id', 'meta_value as campaign_id')
            ->whereIn('donation_id', $parentPaymentIds)
            ->where('meta_key', '_give_campaign_id')
            ->getAll();

        $parentToCampaign = [];
        foreach ($parentCampaigns as $row) {
            $parentToCampaign[$row->donation_id] = (int)$row->campaign_id;
        }

        // Map back to original donations
        $result = [];
        foreach ($donationToSubscription as $donationId => $subscriptionId) {
            if (isset($subscriptionToParent[$subscriptionId])) {
                $parentPaymentId = $subscriptionToParent[$subscriptionId];
                $data = [
                    'subscription_id' => $subscriptionId,
                    'parent_payment_id' => $parentPaymentId,
                ];

                // Only add parent_campaign_id if it exists
                if (isset($parentToCampaign[$parentPaymentId])) {
                    $data['parent_campaign_id'] = $parentToCampaign[$parentPaymentId];
                }

                $result[$donationId] = $data;
            }
        }

        return $result;
    }

    /**
     * Get form-to-campaign mappings for multiple donations
     *
     * @since 4.3.2
     */
    private function getBulkFormCampaignMappings(array $donationIds, array $revenueCampaigns, array $subscriptionData): array
    {
        // Filter out donations that already have campaign IDs
        $remainingDonationIds = array_filter($donationIds, function($id) use ($revenueCampaigns, $subscriptionData) {
            return !isset($revenueCampaigns[$id]) && !isset($subscriptionData[$id]['parent_campaign_id']);
        });

        if (empty($remainingDonationIds)) {
            return [];
        }

        // First try to get form IDs from parent payments for donations that have subscription data but no parent campaign
        $parentFormIds = [];
        foreach ($subscriptionData as $donationId => $data) {
            if (!isset($revenueCampaigns[$donationId]) && !isset($data['parent_campaign_id']) && isset($data['parent_payment_id'])) {
                $parentFormIds[$donationId] = $data['parent_payment_id'];
            }
        }

        $formMappings = [];

        // Get form IDs from parent payments
        if (!empty($parentFormIds)) {
            $parentForms = DB::table('give_donationmeta')
                ->select('donation_id', 'meta_value as form_id')
                ->whereIn('donation_id', array_values($parentFormIds))
                ->where('meta_key', '_give_payment_form_id')
                ->getAll();

            foreach ($parentForms as $row) {
                // Map back to original donation ID
                $originalDonationId = array_search($row->donation_id, $parentFormIds);
                if ($originalDonationId !== false) {
                    $formMappings[$originalDonationId] = (int)$row->form_id;
                }
            }
        }

        // Get form IDs directly from remaining donations
        $directFormIds = DB::table('give_donationmeta')
            ->select('donation_id', 'meta_value as form_id')
            ->whereIn('donation_id', $remainingDonationIds)
            ->where('meta_key', '_give_payment_form_id')
            ->getAll();

        foreach ($directFormIds as $row) {
            if (!isset($formMappings[$row->donation_id])) {
                $formMappings[$row->donation_id] = (int)$row->form_id;
            }
        }

        if (empty($formMappings)) {
            return [];
        }

        // Get campaign mappings for all form IDs
        $formIds = array_unique(array_values($formMappings));
        $formToCampaign = DB::table('give_campaign_forms')
            ->select('form_id', 'campaign_id')
            ->whereIn('form_id', $formIds)
            ->getAll();

        $formCampaignMap = [];
        foreach ($formToCampaign as $row) {
            $formCampaignMap[$row->form_id] = (int)$row->campaign_id;
        }

        // Map back to donations
        $result = [];
        foreach ($formMappings as $donationId => $formId) {
            if (isset($formCampaignMap[$formId])) {
                $result[$donationId] = $formCampaignMap[$formId];
            }
        }

        return $result;
    }

    /**
     * Bulk insert meta data while avoiding duplicates
     *
     * @since 4.3.2
     */
    private function bulkInsertMeta(array $metaInserts): void
    {
        if (empty($metaInserts)) {
            return;
        }

        global $wpdb;

        // Extract donation IDs to check for existing meta
        $donationIds = array_column($metaInserts, 'donation_id');

        // Check which donations already have the meta key
        $existingMeta = DB::table('give_donationmeta')
            ->select('donation_id')
            ->whereIn('donation_id', $donationIds)
            ->where('meta_key', '_give_campaign_id')
            ->getAll();

        $existingDonationIds = array_column($existingMeta, 'donation_id');

        // Filter out donations that already have the meta key
        $filteredMetaInserts = array_filter($metaInserts, function($meta) use ($existingDonationIds) {
            return !in_array($meta['donation_id'], $existingDonationIds);
        });

        if (empty($filteredMetaInserts)) {
            return;
        }

        // Perform bulk insert for remaining donations
        $values = [];
        $placeholders = [];

        foreach ($filteredMetaInserts as $meta) {
            $values[] = $meta['donation_id'];
            $values[] = $meta['meta_key'];
            $values[] = $meta['meta_value'];
            $placeholders[] = '(%d, %s, %s)';
        }

        $sql = "INSERT INTO {$wpdb->prefix}give_donationmeta (donation_id, meta_key, meta_value) VALUES " .
               implode(', ', $placeholders);

        $wpdb->query($wpdb->prepare($sql, $values));
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
