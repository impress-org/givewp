<?php

namespace Give\Campaigns\Routes;

use Give\API\RestRoute;
use Give\Campaigns\ListTable\CampaignsListTable;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;
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
                    //'callback' => [$this, 'handleRequestMockup'],
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
    public function handleRequestMockup(WP_REST_Request $request): WP_REST_Response
    {
        $items = [
            [
                'id' => 1,
                'title' => '<a href="https://givewp.local/wp-admin/edit.php?post_type=give_forms&page=campaigns&id=1" aria-label="Visit Campaigns page">Campaign 1</a>',
                'description' => 'This is the campaign 1',
                'donationsCount' => '8',
                'startDate' => '06/05/2024 at 11:00am',
                'status' => 'DRAFT',
            ],
            [
                'id' => 2,
                'title' => '<a href="https://givewp.local/wp-admin/edit.php?post_type=give_forms&page=campaigns&id=2" aria-label="Visit Campaigns page">Campaign 2</a>',
                'description' => 'This is the campaign 2',
                'donationsCount' => '16',
                'startDate' => '06/05/2024 at 11:00am',
                'status' => 'PUBLISHED',
            ],
        ];

        return new WP_REST_Response(
            [
                'items' => $items,
                'totalItems' => count($items),
                'totalPages' => 1,
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

        if ('model' === $this->request->get_param('return')) {
            $items = $campaigns;
        } else {
            $this->listTable->items($campaigns, $this->request->get_param('locale') ?? '');
            $items = $this->listTable->getItems();
        }

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
        $query = DB::table('give_campaigns');
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
                $query->whereLike('title', $search);
                $query->orWhereLike('shortDescription', $search);
            }
        }

        return $query;
    }

    /**
     * @unreleased
     *
     * @return bool|\WP_Error
     */
    public function permissionsCheck()
    {
        return current_user_can('edit_posts') ?: new \WP_Error(
            'rest_forbidden',
            esc_html__("You don't have permission to view Campaigns", 'give'),
            ['status' => is_user_logged_in() ? 403 : 401]
        );
    }
}
