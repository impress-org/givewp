<?php

namespace Give\Campaigns\Controllers;

use Give\Campaigns\DataTransferObjects\CampaignDetailsData;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\Campaigns\ValueObjects\CampaignRoute;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\Framework\Exceptions\Primitives\Exception;
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
            (new CampaignDetailsData($campaign))->toArray()
        );
    }

    /**
     * @unreleased
     */
    public function getCampaigns(WP_REST_Request $request): WP_REST_Response
    {
        $page = $request->get_param('page');
        $perPage = $request->get_param('per_page');

        $query = give(CampaignRepository::class)->prepareQuery();

        $query
            ->limit($perPage)
            ->offset(($page - 1) * $perPage);

        $campaigns = $query->getAll() ?? [];
        $totalCampaigns = empty($campaigns) ? 0 : Campaign::query()->count();
        $totalPages = (int)ceil($totalCampaigns / $perPage);

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
                case 'defaultForm':
                    give(CampaignRepository::class)->updateDefaultCampaignForm(
                        $campaign,
                        $request->get_param('defaultForm')
                    );
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
            (new CampaignDetailsData($campaign))->toArray()
        );
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
}
