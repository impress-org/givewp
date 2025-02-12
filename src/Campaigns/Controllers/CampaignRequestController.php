<?php

namespace Give\Campaigns\Controllers;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\Campaigns\ValueObjects\CampaignRoute;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\Framework\Database\DB;
use Give\Framework\Models\ModelQueryBuilder;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
 */
class CampaignRequestController
{
    /**
     * @unreleased
     *
     * @return WP_Error | WP_REST_Response
     */
    public function getCampaign(WP_REST_Request $request)
    {
        $campaign = Campaign::find($request->get_param('id'));

        if ( ! $campaign) {
            return new WP_Error('campaign_not_found', __('Campaign not found', 'give'), ['status' => 404]);
        }

        return new WP_REST_Response(
            array_merge($campaign->toArray(), [
                'goalStats' => $campaign->getGoalStats(),
                'defaultFormTitle' => $campaign->defaultForm()->title,
            ])
        );
    }

    /**
     * @unreleased
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

        $query->where('status', $status);

        if ( ! empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $totalQuery = clone $query;

        $query
            ->limit($perPage)
            ->offset(($page - 1) * $perPage);

        $this->orderCampaigns($query, $sortBy, $orderBy);

        $campaigns = $query->getAll() ?? [];
        $totalCampaigns = empty($campaigns) ? 0 : $totalQuery->count();
        $totalPages = (int)ceil($totalCampaigns / $perPage);

        $campaigns = array_map(function ($campaign) {
            return array_merge($campaign->toArray(), [
                'goalStats' => $campaign->getGoalStats(),
            ]);
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
     * @unreleased
     *
     * @return WP_Error | WP_REST_Response
     *
     * @throws Exception
     */
    public function updateCampaign(WP_REST_Request $request)
    {
        $campaign = Campaign::find($request->get_param('id'));

        if ( ! $campaign) {
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
                    give(CampaignRepository::class)->updateDefaultCampaignForm($campaign,
                        $request->get_param('defaultFormId'));
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

        return new WP_REST_Response(
            array_merge($campaign->toArray(), [
                'defaultFormTitle' => $campaign->defaultForm()->title,
            ])
        );
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function mergeCampaigns(WP_REST_Request $request): WP_REST_Response
    {
        $destinationCampaign = Campaign::find($request->get_param('id'));
        $campaignsToMerge = Campaign::query()->whereIn('id', $request->get_param('campaignsToMergeIds'))->getAll();

        $campaignsMerged = $destinationCampaign->merge(...$campaignsToMerge);

        return new WP_REST_Response($campaignsMerged);
    }


    /**
     * @unreleased
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
            'primaryColor' => '',
            'secondaryColor' => '',
            'goal' => (int)$request->get_param('goal'),
            'goalType' => new CampaignGoalType($request->get_param('goalType')),
            'status' => CampaignStatus::DRAFT(),
            'startDate' => $request->get_param('startDateTime'),
            'endDate' => $request->get_param('endDateTime'),
        ]);

        return new WP_REST_Response($campaign->toArray(), 201);
    }

    /**
     * @unreleased
     */
    private function orderCampaigns(ModelQueryBuilder $query, $sortBy, $orderBy)
    {
        switch ($sortBy) {
            case 'date':
                $query->orderBy('date_created', $orderBy);

                break;
            case 'amount':
                $query
                    ->selectRaw('(SELECT SUM(amount) FROM %1s WHERE campaign_id = campaigns.id) AS amount',
                        DB::prefix('give_revenue'))
                    ->orderBy('amount', $orderBy);

                break;
            case 'donations':
                $query
                    ->selectRaw('(SELECT COUNT(donation_id) FROM %1s WHERE campaign_id = campaigns.id) AS donationsCount',
                        DB::prefix('give_revenue'))
                    ->orderBy('donationsCount', $orderBy);

                break;
        }
    }
}
