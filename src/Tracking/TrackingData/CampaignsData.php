<?php

namespace Give\Tracking\TrackingData;

use Give\Campaigns\Models\Campaign;
use Give\Framework\Database\DB;
use Give\Tracking\Contracts\TrackData;
use Give\Tracking\Repositories\TrackEvents;

/**
 * @since 4.3.2
 */
class CampaignsData implements TrackData
{
    /**
     * @var TrackEvents
     */
    protected $trackEvents;

    /**
     * DonationFormsData constructor.
     *
     * @param TrackEvents $trackEvents
     */
    public function __construct(TrackEvents $trackEvents)
    {
        $this->trackEvents = $trackEvents;
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        $query = Campaign::query()
            ->select('count(forms.form_id) as formCount')
            ->leftJoin('give_campaign_forms', 'campaigns.id', 'forms.campaign_id', 'forms')
            ->groupBy('campaigns.id');

        return array_map(function($campaign) {
            return [
                'campaign_id' => $campaign->id,
                'campaign_status' => $campaign->status,
                'campaign_form_count' => $campaign->formCount,
            ];
        }, DB::get_results($query->getSQL()));
    }
}
