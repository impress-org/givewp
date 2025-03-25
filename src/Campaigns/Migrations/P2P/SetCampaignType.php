<?php

namespace Give\Campaigns\Migrations\P2P;

use Give\Campaigns\ValueObjects\CampaignType;
use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * @unreleased
 *
 * Set campaign type for existing P2P campaign
 */
class SetCampaignType extends Migration
{
    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'give-campaigns-set-campaign-type';
    }

    /**
     * @inheritdoc
     */
    public static function title(): string
    {
        return 'Set campaign type for existing P2P campaigns';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp(): string
    {
        return strtotime('2024-08-26 00:00:02');
    }

    /**
     * @inheritDoc
     * @throws DatabaseMigrationException
     */
    public function run()
    {
        try {
            DB::table('give_campaigns')
                ->where('campaign_type', '')
                ->update([
                    'campaign_type' => CampaignType::PEER_TO_PEER
                ]);
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException('An error occurred while updating the campaign type', 0, $exception);
        }
    }
}
