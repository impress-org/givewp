<?php

namespace Give\Campaigns\Routes;

use Give\API\RestRoute;
use Give\Campaigns\ListTable\CampaignsListTable;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @unreleased
 */
class GetCampaignsListTable implements RestRoute
{
    /**
     * @var string
     */
    protected $endpoint = 'campaigns/list-table';

    /**
     * @var WP_REST_Request
     */
    protected $request;

    /**
     * @var CampaignsListTable
     */
    protected $listTable;

    /**
     * @unreleased
     */
    public function registerRoute(): void
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => [
                    'page' => [
                        'type' => 'integer',
                        'default' => 1,
                        'minimum' => 1,
                    ],
                    'perPage' => [
                        'type' => 'integer',
                        'default' => 30,
                        'minimum' => 1,
                    ],
                    'search' => [
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'sortColumn' => [
                        'type' => 'string',
                        'default' => 'id',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'sortDirection' => [
                        'type' => 'string',
                        'default' => 'asc',
                        'enum' => ['asc', 'desc'],
                    ],
                    'locale' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => get_locale(),
                    ],
                ],
            ]
        );
    }

    /**
     * @unreleased
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $this->request = $request;
        $this->listTable = give(CampaignsListTable::class);

        $campaigns = $this->getCampaigns();
        $campaignsCount = $this->getTotalCampaignsCount();
        $pageCount = (int)ceil($campaignsCount / $request->get_param('perPage'));

        $this->listTable->items($campaigns, $this->request->get_param('locale') ?? '');
        $items = $this->listTable->getItems();


        return new WP_REST_Response(
            [
                'items' => $items,
                'totalItems' => $campaignsCount,
                'totalPages' => $pageCount,
            ]
        );
    }

    /**
     * @unreleased
     */
    public function getCampaigns(): array
    {
        $page = $this->request->get_param('page');
        $perPage = $this->request->get_param('perPage');
        $sortColumns = $this->listTable->getSortColumnById($this->request->get_param('sortColumn') ?: 'id');
        $sortDirection = $this->request->get_param('sortDirection') ?: 'desc';

        $query = give(CampaignRepository::class)->prepareQuery();
        $query = $this->getWhereConditions($query);

        foreach ($sortColumns as $sortColumn) {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $query->limit($perPage)
            ->offset(($page - 1) * $perPage);

        $campaigns = $query->getAll();

        if ( ! $campaigns) {
            return [];
        }

        return $campaigns;
    }

    /**
     * @unreleased
     */
    public function getTotalCampaignsCount(): int
    {
        $query = Campaign::query();
        $query = $this->getWhereConditions($query);

        return $query->count();
    }

    /**
     * @unreleased
     */
    private function getWhereConditions(QueryBuilder $query): QueryBuilder
    {
        $search = $this->request->get_param('search');

        if ($search) {
            if (ctype_digit($search)) {
                $query->where('id', $search);
            } else {
                $query->whereLike('campaign_title', $search);
                $query->orWhereLike('short_desc', $search);
            }
        }

        return $query;
    }

    /**
     * @unreleased
     *
     * @return bool|WP_Error
     */
    public function permissionsCheck()
    {
        return current_user_can('edit_posts') ?: new WP_Error(
            'rest_forbidden',
            esc_html__("You don't have permission to view Campaigns", 'give'),
            ['status' => is_user_logged_in() ? 403 : 401]
        );
    }
}
