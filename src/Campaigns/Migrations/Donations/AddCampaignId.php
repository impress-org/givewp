<?php

namespace Give\Campaigns\Migrations\Donations;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\BatchMigration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * @unreleased
 */
class AddCampaignId extends BatchMigration
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'add_campaign_id_to_donations';
    }

    /**
     * @inheritDoc
     */
    public static function title(): string
    {
        return 'Add campaign id to donations';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp(): string
    {
        return strtotime('2024-11-22 00:00:00');
    }

    /**
     * @inheritDoc
     * @throws DatabaseMigrationException
     */
    public function runBatch($batchNumber)
    {
        $relationships = [];

        try {
            $data = DB::table('give_campaign_forms')
                ->select('campaign_id', 'form_id')
                ->getAll();

            foreach ($data as $relationship) {
                $relationships[$relationship->campaign_id][] = $relationship->form_id;
            }

            $donations = DB::table('posts')
                ->select('ID')
                ->attachMeta(
                    'give_donationmeta',
                    'ID',
                    'donation_id',
                    [DonationMetaKeys::FORM_ID(), 'formId']
                )
                ->where('post_type', 'give_payment')
                ->offset($batchNumber)
                ->limit($this->getBatchSize())
                ->getAll();

            $donationMeta = [];

            foreach ($donations as $donation) {
                foreach ($relationships as $campaignId => $formIds) {
                    if (in_array($donation->formId, $formIds)) {
                        $donationMeta[] = [
                            'donation_id' => $donation->ID,
                            'meta_key' => DonationMetaKeys::CAMPAIGN_ID,
                            'meta_value' => $campaignId,
                        ];

                        break;
                    }
                }
            }

            if ( ! empty($donationMeta)) {
                DB::table('give_donationmeta')
                    ->insert($donationMeta, ['%d', '%s', '%d']);
            }
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException("An error occurred while adding campaign ID to the donation meta table",
                0, $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function getItemsCount(): int
    {
        return DB::table('posts')
            ->where('post_type', 'give_payment')
            ->count();
    }

    /**
     * @inheritDoc
     */
    public function getBatchSize(): int
    {
        return 50;
    }
}
