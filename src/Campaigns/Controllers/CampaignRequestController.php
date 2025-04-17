<?php

namespace Give\Campaigns\Controllers;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Models\CampaignPage;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Campaigns\Repositories\CampaignsDataRepository;
use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\Campaigns\ValueObjects\CampaignPageStatus;
use Give\Campaigns\ValueObjects\CampaignRoute;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\Campaigns\ViewModels\CampaignViewModel;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Database\DB;
use Give\Framework\Models\ModelQueryBuilder;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 4.0.0
 */
class CampaignRequestController
{
    /**
     * @since 4.0.0
     *
     * @return WP_Error | WP_REST_Response
     */
    public function getCampaign(WP_REST_Request $request)
    {
        $campaign = Campaign::find($request->get_param('id'));

        if (!$campaign) {
            return new WP_Error('campaign_not_found', __('Campaign not found', 'give'), ['status' => 404]);
        }

        return new WP_REST_Response((new CampaignViewModel($campaign))->exports());
    }

    /**
     * @since 4.0.0
     */
    public function getCampaigns(WP_REST_Request $request): WP_REST_Response
    {
        $ids = $request->get_param('ids');
        $page = $request->get_param('page');
        $perPage = $request->get_param('per_page');
        $status = $request->get_param('status');
        $sortBy = $request->get_param('sortBy');
        $orderBy = $request->get_param('orderBy');

        $query = Campaign::query();

        $query->whereIn('status', $status);

        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $totalQuery = clone $query;

        $query
            ->limit($perPage)
            ->offset(($page - 1) * $perPage);

        $this->orderCampaigns($query, $sortBy, $orderBy);

        $campaigns = $query->getAll() ?? [];
        $totalCampaigns = empty($campaigns) ? 0 : $totalQuery->count();
        $totalPages = $totalCampaigns === 0 ? 0 : (int)ceil($totalCampaigns / $perPage);

        $ids = array_map(function ($campaign) {
            return $campaign->id;
        }, $campaigns);

        // We don't have to optimize if the number of campaigns is less than 3
        $campaignsData = count($ids) >= 3
            ? CampaignsDataRepository::campaigns($ids)
            : null;

        $campaigns = array_map(function ($campaign) use ($campaignsData) {
            $view = new CampaignViewModel($campaign);

            if ($campaignsData) {
                $view->setData($campaignsData);
            }

            return $view->exports();
        }, $campaigns);

        $response = rest_ensure_response($campaigns);
        $response->header('X-WP-Total', $totalCampaigns);
        $response->header('X-WP-TotalPages', $totalPages);

        $base = add_query_arg(
            map_deep($request->get_query_params(), function ($value) {
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }

                return urlencode($value);
            }),
            rest_url(CampaignRoute::CAMPAIGNS)
        );

        if ($page > 1) {
            $prevPage = $page - 1;

            if ($prevPage > $totalPages) {
                $prevPage = $totalPages;
            }

            $response->link_header('prev', add_query_arg('page', $prevPage, $base));
        }

        if ($totalPages > $page) {
            $nextPage = $page + 1;
            $response->link_header('next', add_query_arg('page', $nextPage, $base));
        }

        return $response;
    }

    /**
     * @since 4.0.0
     *
     * @return WP_Error | WP_REST_Response
     *
     * @throws Exception
     */
    public function updateCampaign(WP_REST_Request $request)
    {
        $campaign = Campaign::find($request->get_param('id'));

        if (!$campaign) {
            return new WP_Error('campaign_not_found', __('Campaign not found', 'give'), ['status' => 404]);
        }

        $statusMap = [
            'archived' => CampaignStatus::ARCHIVED(),
            'draft' => CampaignStatus::DRAFT(),
            'active' => CampaignStatus::ACTIVE(),
        ];

        foreach ($request->get_params() as $key => $value) {
            switch ($key) {
                case 'id':
                    break;
                case 'status':
                    $status = array_key_exists($value, $statusMap)
                        ? $statusMap[$value]
                        : CampaignStatus::DRAFT();

                    $campaign->status = $status;

                    break;
                case 'goal':
                    $campaign->goal = (int)$value;
                    break;
                case 'goalType':
                    $campaign->goalType = new CampaignGoalType($value);
                    break;
                case 'defaultFormId':
                    give(CampaignRepository::class)->updateDefaultCampaignForm(
                        $campaign,
                        $request->get_param('defaultFormId')
                    );
                    break;
                case 'pageId':
                    $campaign->pageId = (int)$value;
                    break;
                default:
                    if ($campaign->hasProperty($key)) {
                        $campaign->$key = $value;
                    }
            }
        }

        if ($campaign->isDirty()) {
            $campaign->save();
        }

        return new WP_REST_Response((new CampaignViewModel($campaign))->exports());
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     * @return WP_Error | WP_REST_Response
     */
    public function mergeCampaigns(WP_REST_Request $request)
    {
        $destinationCampaign = Campaign::find($request->get_param('id'));

        if (!$destinationCampaign) {
            return new WP_Error('campaign_not_found', __('Campaign not found', 'give'), ['status' => 404]);
        }

        $campaignsToMerge = Campaign::query()->whereIn('id', $request->get_param('campaignsToMergeIds'))->getAll();

        $campaignsMerged = $destinationCampaign->merge(...$campaignsToMerge);

        return new WP_REST_Response($campaignsMerged);
    }


    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function createCampaign(WP_REST_Request $request): WP_REST_Response
    {
        $campaign = Campaign::create([
            'type' => CampaignType::CORE(),
            'title' => $request->get_param('title'),
            'shortDescription' => $request->get_param('shortDescription') ?? '',
            'longDescription' => '',
            'logo' => '',
            'image' => $request->get_param('image') ?? '',
            'primaryColor' => '#0b72d9',
            'secondaryColor' => '#27ae60',
            'goal' => (int)$request->get_param('goal'),
            'goalType' => new CampaignGoalType($request->get_param('goalType')),
            'status' => CampaignStatus::ACTIVE(),
            'startDate' => $request->get_param('startDateTime'),
            'endDate' => $request->get_param('endDateTime'),
        ]);

        return new WP_REST_Response((new CampaignViewModel($campaign))->exports(), 201);
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function createCampaignPage(WP_REST_Request $request): WP_REST_Response
    {
        $campaignPage = CampaignPage::create([
            'campaignId' => (int)$request->get_param('id'),
            'status' => CampaignPageStatus::DRAFT(),
        ]);

        return new WP_REST_Response($campaignPage->toArray(), 201);
    }

    /**
     * @since 4.0.0
     */
    private function orderCampaigns(ModelQueryBuilder $query, $sortBy, $orderBy)
    {
        switch ($sortBy) {
            case 'date':
                $query->orderBy('date_created', $orderBy);

                break;
            case 'amount':
                $query
                    ->selectRaw(
                        '(SELECT SUM(amount) FROM %1s WHERE campaign_id = campaigns.id) AS amount',
                        DB::prefix('give_revenue')
                    )
                    ->orderBy('amount', $orderBy);

                break;
            case 'donations':
                $query
                    ->selectRaw(
                        '(SELECT COUNT(donation_id) FROM %1s WHERE campaign_id = campaigns.id) AS donationsCount',
                        DB::prefix('give_revenue')
                    )
                    ->orderBy('donationsCount', $orderBy);

                break;
            case 'donors':

                $postsTable = DB::prefix('posts');
                $metaTable = DB::prefix('give_donationmeta');
                $campaignIdKey = DonationMetaKeys::CAMPAIGN_ID;
                $donorIdKey = DonationMetaKeys::DONOR_ID;

                $query
                    ->selectRaw(
                        "(
                            SELECT COUNT(DISTINCT donorId.meta_value)
                            FROM {$postsTable} AS donation
                            LEFT JOIN {$metaTable} campaignId ON donation.ID = campaignId.donation_id AND campaignId.meta_key = '{$campaignIdKey}'
                            LEFT JOIN {$metaTable} donorId ON donation.ID = donorId.donation_id AND donorId.meta_key = '{$donorIdKey}'
                            WHERE post_type = 'give_payment'
                            AND donation.post_status IN ('publish', 'give_subscription')
                            AND campaignId.meta_value = campaigns.id
                        ) AS donorsCount"
                    )
                    ->orderBy('donorsCount', $orderBy);

                break;
        }
    }
}
