<?php

namespace Give\Campaigns\ViewModels;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignsDataRepository;
use Give\Campaigns\ValueObjects\CampaignPageMetaKeys;
use Give\Framework\Database\DB;
use Give\Framework\Support\Facades\DateTime\Temporal;

/**
 * @since 4.0.0
 */
class CampaignViewModel
{
    /**
     * @var Campaign
     */
    private $campaign;

    /**
     * @var CampaignsDataRepository|null
     */
    private $data;

    /**
     * @since 4.0.0
     */
    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Set data source
     *
     * @param CampaignsDataRepository $data
     *
     * @return CampaignViewModel
     */
    public function setData(CampaignsDataRepository $data): CampaignViewModel
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @since 4.0.0
     */
    public function exports(): array
    {
        $pagePermalink = $this->getPagePermalink();

        return [
            'id' => $this->campaign->id,
            'pageId' => $pagePermalink ? (int)$this->campaign->pageId : null,
            'pagePermalink' => $pagePermalink,
            'defaultFormId' => $this->campaign->defaultFormId,
            'defaultFormTitle' => $this->campaign->defaultForm()->title,
            'type' => $this->campaign->type->getValue(),
            'title' => $this->campaign->title,
            'shortDescription' => $this->campaign->shortDescription,
            'longDescription' => $this->campaign->longDescription,
            'logo' => $this->campaign->logo,
            'image' => $this->campaign->image,
            'primaryColor' => $this->campaign->primaryColor,
            'secondaryColor' => $this->campaign->secondaryColor,
            'goal' => $this->campaign->goal,
            'goalType' => $this->campaign->goalType->getValue(),
            'goalStats' => is_null($this->data)
                ? $this->campaign->getGoalStats()
                : $this->data->getGoalData($this->campaign),
            'status' => $this->campaign->status->getValue(),
            'startDate' => Temporal::getFormattedDateTime($this->campaign->startDate),
            'endDate' => $this->campaign->endDate
                ? Temporal::getFormattedDateTime($this->campaign->endDate)
                : null,
            'createdAt' => Temporal::getFormattedDateTime($this->campaign->createdAt),
        ];
    }

    /**
     * @since 4.0.0
     */
    protected function getPagePermalink(): ?string
    {
        $page = get_post($this->campaign->pageId);
        if (!$page){
            return null;
        }

        if ($page->post_status === 'trash') {
            return null;
        }

        $permalink = get_permalink($this->campaign->pageId ?? 0);

        if ($permalink){
            return $permalink;
        }

        $query = DB::table('postmeta')
            ->select('post_id')
            ->where('meta_key', CampaignPageMetaKeys::CAMPAIGN_ID)
            ->where('meta_value', $this->campaign->id)
            ->get();

        if (!$query) {
            return null;
        }

        return get_permalink($query->post_id);
    }
}
