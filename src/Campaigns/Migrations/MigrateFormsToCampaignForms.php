<?php

namespace Give\Campaigns\Migrations;

use Give\Campaigns\Actions\CreateParentCampaignForDonationForm;
use Give\DonationForms\Models\DonationForm;
use Give\Framework\Migrations\Contracts\Migration;

/**
 * @unreleased
 */
class MigrateFormsToCampaignForms extends Migration
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'migrate_forms_to_campaign_forms';
    }

    /**
     * @inheritDoc
     */
    public static function timestamp(): int
    {
        return strtotime('2024-08-21');
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        array_map(
            [new CreateParentCampaignForDonationForm, '__invoke'],
            DonationForm::query()->getAll() ?? []
        );
    }
}
