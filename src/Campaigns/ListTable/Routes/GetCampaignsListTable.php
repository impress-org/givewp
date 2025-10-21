<?php

namespace Give\Campaigns\ListTable\Routes;

use Give\API\REST\V3\Routes\Campaigns\ValueObjects\CampaignRoute;
use Give\API\RestRoute;
use Give\Campaigns\ListTable\CampaignsListTable;
use Give\Campaigns\ListTable\Columns\RevenueColumn;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Campaigns\Repositories\CampaignsDataRepository;
use Give\Framework\QueryBuilder\QueryBuilder;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @since 4.0.0
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
     * @since 4.0.0
     */
    public function registerRoute(): void
    {
        register_rest_route(
            CampaignRoute::NAMESPACE,
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
                    'status' => [
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
     * @since 4.12.0 add support for sorting by revenue column
     * @since 4.0.0
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $this->request = $request;
        $this->listTable = give(CampaignsListTable::class);
        $sortColumns = $this->listTable->getSortColumnById($request->get_param('sortColumn') ?: 'id');
        $sortDirection = $request->get_param('sortDirection') ?: 'desc';

        $campaignsCount = $this->getTotalCampaignsCount();

        if ($campaignsCount === 0) {
            return new WP_REST_Response(
                [
                    'items' => [],
                    'totalItems' => 0,
                    'totalPages' => 0,
                ]
            );
        }

        $campaigns = $this->getCampaigns();
        $pageCount = (int)ceil($campaignsCount / $request->get_param('perPage'));

        $ids = array_map(function (Campaign $campaign) {
            return $campaign->id;
        }, $campaigns);

        $campaignsData = CampaignsDataRepository::campaigns($ids);

        // Sort by revenue column
        if (in_array(RevenueColumn::getId(), $sortColumns)) {
            usort($campaigns, function(Campaign $a, Campaign $b) use ($campaignsData, $sortDirection) {
                return $sortDirection === 'asc'
                    ? $campaignsData->getRevenue($a) <=> $campaignsData->getRevenue($b)
                    : $campaignsData->getRevenue($b) <=> $campaignsData->getRevenue($a);
            });
        }

        $this->listTable
            ->setData($campaignsData)
            ->items($campaigns, $this->request->get_param('locale') ?? '');

        $items = $this->listTable->getItems();

        foreach ($items as $i => $item) {
            $items[$i]['titleRaw'] = $campaigns[$i]->title;
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
     * @since 4.12.0 remove revenue column from sort
     * @since 4.0.0
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
            if (RevenueColumn::getId() === $sortColumn) {
                continue;
            }

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
     * @since 4.0.0
     */
    public function getTotalCampaignsCount(): int
    {
        $query = Campaign::query();
        $query = $this->getWhereConditions($query);

        return $query->count();
    }

    /**
     * @since 4.0.0
     */
    private function getWhereConditions(QueryBuilder $query): QueryBuilder
    {
        $search = $this->request->get_param('search');
        $status = $this->request->get_param('status');

        if ($search) {
            if (ctype_digit($search)) {
                $query->where('id', $search);
            } else {
                $query->whereLike('campaign_title', $search);
                $query->orWhereLike('short_desc', $search);
            }
        }

        if ($status === 'any') {
            $query->where('status', 'archived', '!=');
        } elseif ($status === 'inactive') {
            $query->where('status', 'archived');
        } elseif ($status) {
            $query->where('status', $status);
        }

        return $query;
    }

    /**
     * @since 4.3.1 update permissions
     * @since 4.0.0
     *
     * @return bool|WP_Error
     */
    public function permissionsCheck()
    {
        if (current_user_can('manage_options') || current_user_can('edit_give_forms')) {
            return true;
        }

        return new WP_Error(
            'rest_forbidden',
            esc_html__("You don't have permission to view Campaigns", 'give'),
            ['status' => is_user_logged_in() ? 403 : 401]
        );
    }
}
