<?php

namespace Give\Campaigns\Migrations;

use Give\Campaigns\Actions\CreateParentCampaignForDonationForm;
use Give\Campaigns\Models\Campaign;
use Give\DonationForms\Models\DonationForm;
use Give\Framework\Database\DB;
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
     * @unreleased
     * @inheritDoc
     */
    public function run()
    {
        foreach(DonationForm::query()->getAll() ?? [] as $form) {
            $this->createParentCampaignForDonationForm($form);
        }
    }

    /**
     * @unreleased
     */
    public function createParentCampaignForDonationForm($form)
    {
        $campaign = Campaign::create([
            'title' => $form->title,
            'goal' => $form->setting->goalAmount, // TODO: Reconcile form float goalAmount wih campaign integer goal.
            'status' => $form->status, // TODO: Map form status to campaign status.
        ]);

        DB::table('give_campaign_forms')
            ->insert([
                'form_id' => $form->id,
                'campaign_id' => $campaign->id,
            ]);
    }
}
