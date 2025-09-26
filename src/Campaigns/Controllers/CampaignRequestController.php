<?php

namespace Give\Campaigns\Controllers;

use Exception;
use Give\API\REST\V3\Routes\Campaigns\ValueObjects\CampaignRoute;
use Give\API\REST\V3\Support\CURIE;
use Give\API\REST\V3\Support\Headers;
use Give\API\REST\V3\Support\Item;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Models\CampaignPage;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Campaigns\Repositories\CampaignsDataRepository;
use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\Campaigns\ValueObjects\CampaignPageStatus;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\Campaigns\ViewModels\CampaignViewModel;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Database\DB;
use Give\Framework\Models\ModelQueryBuilder;
use Give_Form_Duplicator;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Controller;

/**
 * @since 4.0.0
 */
class CampaignRequestController extends WP_REST_Controller
{
    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $rest_base;

    /**
     * @unreleased
     */
    public function __construct()
    {
        $this->namespace = CampaignRoute::NAMESPACE;
        $this->rest_base = CampaignRoute::CAMPAIGNS;
    }

    /**
     * @unreleased prepare item for response
     * @since 4.0.0
     *
     * @return WP_Error | WP_REST_Response
     */
    public function getCampaign(WP_REST_Request $request)
    {
        $campaign = Campaign::find($request->get_param('id'));

        if ( ! $campaign) {
            return new WP_Error('campaign_not_found', __('Campaign not found', 'give'), ['status' => 404]);
        }

        $item = (new CampaignViewModel($campaign))->exports();
        $response = $this->prepare_item_for_response($item, $request);

        return rest_ensure_response($response);
    }

    /**
     * @unreleased prepare items for response
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
        $search = $request->get_param('search');

        $query = Campaign::query();

        $query->whereIn('status', $status);

        if ( ! empty($ids)) {
            $query->whereIn('id', $ids);
        }

        if ($search) {
            $query->whereLike('campaign_title', '%%' . $search . '%%');
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

        $campaignsData = ! empty($ids)
            ? CampaignsDataRepository::campaigns($ids)
            : null;

        $campaigns = array_map(function ($campaign) use ($campaignsData, $request) {
            $view = new CampaignViewModel($campaign);

            if ($campaignsData) {
                $view->setData($campaignsData);
            }

            $item = $view->exports();

            return $this->prepare_response_for_collection(
                $this->prepare_item_for_response($item, $request)
            );
        }, $campaigns);

        $response = rest_ensure_response($campaigns);
        $response = Headers::addPagination($response, $request, $totalCampaigns, $perPage, $this->rest_base);

        return $response;
    }

    /**
     * @unreleased prepare item for response
     * @since 4.0.0
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

        $item = (new CampaignViewModel($campaign))->exports();
        $response = $this->prepare_item_for_response($item, $request);

        return rest_ensure_response($response);
    }

    /**
     * @since 4.0.0
     *
     * @return WP_Error | WP_REST_Response
     * @throws Exception
     */
    public function mergeCampaigns(WP_REST_Request $request)
    {
        $destinationCampaign = Campaign::find($request->get_param('id'));

        if ( ! $destinationCampaign) {
            return new WP_Error('campaign_not_found', __('Campaign not found', 'give'), ['status' => 404]);
        }

        $campaignsToMerge = Campaign::query()->whereIn('id', $request->get_param('campaignsToMergeIds'))->getAll();

        $campaignsMerged = $destinationCampaign->merge(...$campaignsToMerge);

        return new WP_REST_Response($campaignsMerged);
    }


    /**
     * @unreleased prepare item for response
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

        $item = (new CampaignViewModel($campaign))->exports();
        $response = $this->prepare_item_for_response($item, $request);
        $response->set_status(201);

        return rest_ensure_response($response);
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
     * @since 4.2.0
     *
     * @throws Exception
     */
    public function duplicateCampaign(WP_REST_Request $request): WP_REST_Response
    {
        require_once(GIVE_PLUGIN_DIR . '/includes/admin/forms/class-give-form-duplicator.php');

        $campaign = Campaign::find($request->get_param('id'));

        if ( ! $campaign) {
            return new WP_REST_Response('Campaign does not exist', 404);
        }

        $forms = $campaign->forms();
        $campaignRepository = give(CampaignRepository::class);

        $campaign->id = null;
        $campaign->title = sprintf(__('%s (copy)', 'give'), $campaign->title);
        $campaign->save();

        foreach ($forms->getAll() as $form) {
            if ( ! $post = get_post($form->id)) {
                continue;
            }

            $isDefaultForm = $campaign->defaultFormId === $form->id;

            $newFormId = wp_insert_post([
                'comment_status' => $post->comment_status,
                'ping_status' => $post->ping_status,
                'post_author' => get_current_user_id(),
                'post_content' => $post->post_content,
                'post_date_gmt' => current_time('mysql', true),
                'post_excerpt' => $post->post_excerpt,
                'post_name' => $post->post_name,
                'post_parent' => $post->post_parent,
                'post_password' => $post->post_password,
                'post_status' => $isDefaultForm ? 'publish' : 'draft',
                'post_title' => $post->post_title,
                'post_type' => $post->post_type,
                'to_ping' => $post->to_ping,
                'menu_order' => $post->menu_order,
            ]);

            Give_Form_Duplicator::duplicate_taxonomies($newFormId, $post);
            Give_Form_Duplicator::duplicate_meta_data($newFormId, $post);
            Give_Form_Duplicator::reset_stats($newFormId);

            if ($isDefaultForm) {
                $campaign->defaultFormId = $newFormId;
            }

            $campaignRepository->addCampaignForm($campaign, $newFormId, $isDefaultForm);
        }

        if ($campaignPage = CampaignPage::find($campaign->pageId)) {
            $campaignPage->id = null;
            $campaignPage->status = CampaignPageStatus::DRAFT();
            $campaignPage->campaignId = $campaign->id;

            // update campaign id attribute
            $campaignPage->content = preg_replace(
                '/"campaignId":(\d+)/',
                '"campaignId":' . $campaign->id,
                $campaignPage->content
            );

            $campaignPage->save();

            $campaign->pageId = $campaignPage->id;
        }

        $campaign->save();

        return new WP_REST_Response([
            'errors' => 0, // needed by the list table
        ], 201);
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

        /**
     * @unreleased added embeddable links for campaign and form
     * @since 4.8.0
     *
     * @param mixed           $item    WordPress representation of the item.
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function prepare_item_for_response($item, $request)
    {
        try {
            $campaignId = $request->get_param('id') ?? $item['id'] ?? null;

            if ($campaignId && $campaign = Campaign::find($campaignId)) {
                $self_url = rest_url(sprintf('%s/%s/%d', $this->namespace, $this->rest_base, $campaign->id));

                $links = [
                    'self' => ['href' => $self_url]
                ];

                /*
                 * Campaign forms
                 */
                $forms_url = rest_url(sprintf('%s/%s', $this->namespace, 'forms'));
                $forms_url = add_query_arg([
                    'campaignId' => $campaign->id,
                ], $forms_url);

                $links[CURIE::relationUrl('forms')] = [
                    'href' => $forms_url,
                    'embeddable' => true,
                ];
            } else {
                $links = [];
            }

            $response = new WP_REST_Response(Item::formatDatesForResponse($item, ['createdAt', 'renewsAt']));
            if (!empty($links)) {
                $response->add_links($links);
            }

            $response->data = $this->add_additional_fields_to_object($response->data, $request);

            return $response;
        } catch (Exception $e) {
            return new WP_Error(
                'prepare_item_for_response_error',
                sprintf(
                    __('Error while preparing campaign for response: %s', 'give'),
                    $e->getMessage()
                ),
                ['status' => 400]
            );
        }
    }
}
