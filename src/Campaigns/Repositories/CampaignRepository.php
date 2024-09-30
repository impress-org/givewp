<?php

namespace Give\Campaigns\Repositories;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Helpers\Hooks;
use Give\Log\Log;

/**
 * @unreleased
 */
class CampaignRepository
{
    /**
     * @unreleased
     *
     * @var string[]
     */
    private $requiredProperties = [
        'title',
        'status',
    ];

    /**
     * Get Campaign by ID
     */
    public function getById(int $id)
    {
        return $this->prepareQuery()
            ->where('id', $id)
            ->get();
    }

    /**
     * @unreleased
     *
     * @throws Exception|InvalidArgumentException
     */
    public function insert(Campaign $campaign, DonationForm $defaultDonationForm = null): void
    {
        $this->validateProperties($campaign);

        if (is_null($defaultDonationForm)) {
            $defaultDonationForm = DonationForm::factory()->create([
                'title' => $campaign->title,
                'status' => DonationFormStatus::DRAFT(),
            ]);
        }

        Hooks::doAction('givewp_campaign_creating', $campaign, $defaultDonationForm);

        $dateCreated = Temporal::withoutMicroseconds($campaign->createdAt ?: Temporal::getCurrentDateTime());
        $dateCreatedFormatted = Temporal::getFormattedDateTime($dateCreated);
        $startDateFormatted = Temporal::getFormattedDateTime($campaign->startDate);
        $endDateFormatted = Temporal::getFormattedDateTime($campaign->endDate);

        DB::query('START TRANSACTION');

        try {
            DB::table('give_campaigns')
                ->insert([
                    'campaign_type' => $campaign->type->getValue(),
                    'campaign_title' => $campaign->title,
                    'short_desc' => $campaign->shortDescription,
                    'long_desc' => $campaign->longDescription,
                    'campaign_logo' => $campaign->logo,
                    'campaign_image' => $campaign->image,
                    'primary_color' => $campaign->primaryColor,
                    'secondary_color' => $campaign->secondaryColor,
                    'campaign_goal' => $campaign->goal,
                    'goal_type' => $campaign->goalType,
                    'status' => $campaign->status->getValue(),
                    'start_date' => $startDateFormatted,
                    'end_date' => $endDateFormatted,
                    'date_created' => $dateCreatedFormatted
                ]);

            $campaignId = DB::last_insert_id();

            DB::table('give_campaign_forms')
                ->insert([
                    'form_id' => $defaultDonationForm->id,
                    'campaign_id' => $campaignId,
                    'is_default' => true,
                ]);
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed creating a campaign', compact('campaign'));

            throw new $exception('Failed creating a campaign');
        }

        DB::query('COMMIT');

        $campaign->id = $campaignId;
        $campaign->createdAt = $dateCreated;

        Hooks::doAction('givewp_campaign_created', $campaign, $defaultDonationForm);
    }

    /**
     * @unreleased
     *
     * @throws Exception|InvalidArgumentException
     */
    public function update(
        Campaign $campaign,
        DonationForm $donationForm = null,
        $updateDefaultDonationForm = false
    ): void
    {
        $this->validateProperties($campaign);

        $startDateFormatted = Temporal::getFormattedDateTime($campaign->startDate);
        $endDateFormatted = Temporal::getFormattedDateTime($campaign->endDate);

        Hooks::doAction('givewp_campaign_updating', $campaign, $donationForm, $updateDefaultDonationForm);

        DB::query('START TRANSACTION');

        try {
            DB::table('give_campaigns')
                ->where('id', $campaign->id)
                ->update([
                    'campaign_type' => $campaign->type->getValue(),
                    'campaign_title' => $campaign->title,
                    'short_desc' => $campaign->shortDescription,
                    'long_desc' => $campaign->longDescription,
                    'campaign_logo' => $campaign->logo,
                    'campaign_image' => $campaign->image,
                    'primary_color' => $campaign->primaryColor,
                    'secondary_color' => $campaign->secondaryColor,
                    'campaign_goal' => $campaign->goal,
                    'goal_type' => $campaign->goalType,
                    'status' => $campaign->status->getValue(),
                    'start_date' => $startDateFormatted,
                    'end_date' => $endDateFormatted,
                ]);

            if ( ! is_null($donationForm)) {
                // Make sure we won't try to add the same form more than once
                DB::table('give_campaign_forms')
                    ->where('form_id', $donationForm->id)
                    ->where('campaign_id', $campaign->id)
                    ->delete();

                // Make sure we'll have only one default form
                if ($updateDefaultDonationForm) {
                    DB::table('give_campaign_forms')
                        ->where('form_id', $donationForm->id)
                        ->where('campaign_id', $campaign->id)
                        ->update([
                            'is_default' => false,
                        ]);
                }

                DB::table('give_campaign_forms')
                    ->insert([
                        'form_id' => $donationForm->id,
                        'campaign_id' => $campaign->id,
                        'is_default' => $updateDefaultDonationForm,
                    ]);
            }
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed updating a campaign', compact('campaign'));

            throw new $exception('Failed updating a campaign');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_campaign_updated', $campaign, $donationForm, $updateDefaultDonationForm);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function delete(Campaign $campaign): bool
    {
        DB::query('START TRANSACTION');

        Hooks::doAction('givewp_campaign_deleting', $campaign);

        try {
            DB::table('give_campaigns')
                ->where('id', $campaign->id)
                ->delete();

        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed deleting a campaign', compact('campaign'));

            throw new $exception('Failed deleting a campaign');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_campaign_deleted', $campaign);

        return true;
    }

    /**
     * @unreleased
     */
    private function validateProperties(Campaign $campaign): void
    {
        foreach ($this->requiredProperties as $key) {
            if ( ! isset($campaign->$key)) {
                throw new InvalidArgumentException("'$key' is required.");
            }
        }
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<Campaign>
     */
    public function prepareQuery(): ModelQueryBuilder
    {
        $builder = new ModelQueryBuilder(Campaign::class);

        return $builder->from('give_campaigns')
            ->select(
                'id',
                ['campaign_type', 'type'],
                ['campaign_title', 'title'],
                ['short_desc', 'shortDescription'],
                ['long_desc', 'longDescription'],
                ['campaign_logo', 'logo'],
                ['campaign_image', 'image'],
                ['primary_color', 'primaryColor'],
                ['secondary_color', 'secondaryColor'],
                ['campaign_goal', 'goal'],
                ['goal_type', 'goalType'],
                'status',
                ['start_date', 'startDate'],
                ['end_date', 'endDate'],
                ['date_created', 'createdAt']
            )
            // Exclude Peer to Peer campaign type until it is fully supported.
            ->where('campaign_type', CampaignType::PEER_TO_PEER, '!=');
    }
}
