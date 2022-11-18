<?php

namespace Give\DonationForms\Endpoints;

use Give\DonationForms\ListTable\DonationFormsListTable;
use Give\Framework\Models\ModelQueryBuilder;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 2.19.0
 */
class ListForms extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/forms';

    /**
     * @var WP_REST_Request
     */
    private $request;

    /**
     * @var DonationFormsListTable
     */
    protected $listTable;

    /**
     * @inheritDoc
     */
    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => 'GET',
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => [
                    'page' => [
                        'type' => 'integer',
                        'required' => false,
                        'default' => 1,
                        'minimum' => 1
                    ],
                    'perPage' => [
                        'type' => 'integer',
                        'required' => false,
                        'default' => 30,
                        'minimum' => 1
                    ],
                    'status' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'any',
                        'enum' => [
                            'publish',
                            'future',
                            'draft',
                            'pending',
                            'trash',
                            'auto-draft',
                            'inherit',
                            'any'
                        ]
                    ],
                    'search' => [
                        'type' => 'string',
                        'required' => false
                    ]
                ],
            ]
        );
    }

    /**
     * @unreleased Change this to use the new ListTable class
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $this->request = $request;
        $this->listTable = give(DonationFormsListTable::class);

        $forms = $this->getForms();
        $totalForms = $this->getTotalFormsCount();
        $totalPages = (int)ceil($totalForms / $this->request->get_param('perPage'));

        $this->listTable->items($forms);

        return new WP_REST_Response(
            [
                'items' => $this->listTable->getItems(),
                'totalItems' => $totalForms,
                'totalPages' => $totalPages,
                'trash' => defined('EMPTY_TRASH_DAYS') && EMPTY_TRASH_DAYS > 0,
            ]
        );
    }

    /**
     * @unreleased Refactor to query through the ModelQueryBuilder
     *
     * @return array
     */
    public function getForms(): array
    {
        $page = $this->request->get_param('page');
        $perPage = $this->request->get_param('perPage');

        $query = give()->donationForms->prepareQuery();
        $query = $this->getWhereConditions($query);

        $query->limit($perPage)
            ->offset(($page - 1) * $perPage);

        $donationForms = $query->getAll();

        if (!$donationForms) {
            return [];
        }

        return $donationForms;
    }

    /**
     * @unreleased Refactor to query through the ModelQueryBuilder
     *
     * @return int
     */
    public function getTotalFormsCount(): int
    {
        $query = give()->donationForms->prepareQuery();
        $query = $this->getWhereConditions($query);

        return $query->count();
    }

    /**
     * @unreleased
     *
     * @param ModelQueryBuilder $query
     *
     * @return ModelQueryBuilder
     */
    private function getWhereConditions(ModelQueryBuilder $query): ModelQueryBuilder
    {
        $search = $this->request->get_param('search');
        $status = $this->request->get_param('status');

        // Status
        if ($status === 'any') {
            $query->whereIn('post_status', ['publish', 'draft', 'pending', 'private']);
        } else {
            $query->where('post_status', $status);
        }

        // Search
        if ($search) {
            if (ctype_digit($search)) {
                $query->where('ID', $search);
            } else {
                $searchTerms = array_map('trim', explode(' ', $search));
                foreach ($searchTerms as $term) {
                    if ($term) {
                        $query->whereLike('post_title', $term);
                    }
                }
            }
        }

        return $query;
    }
}
