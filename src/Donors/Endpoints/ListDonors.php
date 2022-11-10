<?php

namespace Give\Donors\Endpoints;

use Give\Donors\DataTransferObjects\DonorResponseData;
use Give\Donors\ListTable\DonorsListTable;
use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;
use WP_REST_Request;
use WP_REST_Response;

class ListDonors extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/donors';

    /**
     * @var WP_REST_Request
     */
    protected $request;

    /**
     * @var DonorsListTable
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
                    'donations' => [
                        'type' => 'integer',
                        'required' => false,
                        'default' => 0
                    ],
                    'search' => [
                        'type' => 'string',
                        'required' => false
                    ],
                    'start' => [
                        'type' => 'string',
                        'required' => false,
                        'validate_callback' => [$this, 'validateDate']
                    ],
                    'form' => [
                        'type' => 'integer',
                        'required' => false,
                        'default' => 0
                    ],
                    'end' => [
                        'type' => 'string',
                        'required' => false,
                        'validate_callback' => [$this, 'validateDate']
                    ],
                    'sortColumn' => [
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'sortDirection' => [
                        'type' => 'string',
                        'required' => false,
                        'enum' => [
                            'asc',
                            'desc'
                        ],
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
     * @param WP_REST_Request $request
     * @since 2.20.0
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $this->request = $request;
        $this->listTable = give(DonorsListTable::class);

        $donors = $this->getDonors();
        $donorsCount = $this->getTotalDonorsCount();
        $pageCount = (int)ceil($donorsCount / $request->get_param('perPage'));

        $this->listTable->items($donors, $this->request->get_param('locale'));

        return new WP_REST_Response(
            [
                'items' => $this->listTable->getItems(),
                'totalItems' => $donorsCount,
                'totalPages' => $pageCount
            ]
        );
    }

    /**
     * @since 2.21.0
     *
     * @return array
     */
    public function getDonors(): array
    {
        $page = $this->request->get_param('page');
        $perPage = $this->request->get_param('perPage');
        $sortColumns = $this->listTable->getSortColumnById($this->request->get_param('sortColumn') ?: 'id');
        $sortDirection = $this->request->get_param('sortDirection') ?: 'desc';

        $query = give()->donors->prepareQuery();
        $query = $this->getWhereConditions($query);

        foreach ($sortColumns as $sortColumn) {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $query->limit($perPage)
            ->offset(($page - 1) * $perPage);

        $donors = $query->getAll();

        if (!$donors) {
            return [];
        }

        return $donors;
    }

    /**
     * @since 2.21.0
     *
     * @return int
     */
    public function getTotalDonorsCount(): int
    {
        $query = give()->donors->prepareQuery();
        $query = $this->getWhereConditions($query);

        return $query->count();
    }

    /**
     * @param QueryBuilder $builder
     * @since 2.21.0
     *
     * @return QueryBuilder
     */
    private function getWhereConditions(QueryBuilder $builder): QueryBuilder
    {
        $search = $this->request->get_param('search');
        $start = $this->request->get_param('start');
        $end = $this->request->get_param('end');
        $form = $this->request->get_param('form');

        if ($search) {
            if (ctype_digit($search)) {
                $builder->where('id', $search);
            } else {
                $builder->whereLike('name', $search);
                $builder->orWhereLike('email', $search);
            }
        }

        if ($start && $end) {
            $builder->whereBetween('date_created', $start, $end);
        } else if ($start) {
            $builder->where('date_created', $start, '>=');
        } else if ($end) {
            $builder->where('date_created', $end, '<=');
        }

        if ($form) {
            $builder
                ->whereIn('id', static function (QueryBuilder $builder) use ($form) {
                    $builder
                        ->from('give_donationmeta')
                        ->distinct()
                        ->select('meta_value')
                        ->where('meta_key', '_give_payment_donor_id')
                        ->whereIn('donation_id', static function (QueryBuilder $builder) use ($form) {
                            $builder
                                ->from('give_donationmeta')
                                ->select('donation_id')
                                ->where('meta_key', '_give_payment_form_id')
                                ->where('meta_value', $form);
                        });
                });
        }

        return $builder;
    }
}
