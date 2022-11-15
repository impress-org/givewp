<?php

namespace Give\Donations\Endpoints;

use Give\Donations\ListTable\DonationsListTable;
use Give\Framework\ListTable\Exceptions\ColumnIdCollisionException;
use Give\Framework\Models\ModelQueryBuilder;
use WP_REST_Request;
use WP_REST_Response;

class ListDonations extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/donations';

    /**
     * @var WP_REST_Request
     */
    protected $request;

    /**
     * @var DonationsListTable
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
                    'form' => [
                        'type' => 'integer',
                        'required' => false,
                        'default' => 0
                    ],
                    'search' => [
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'start' => [
                        'type' => 'string',
                        'required' => false,
                        'validate_callback' => [$this, 'validateDate']
                    ],
                    'end' => [
                        'type' => 'string',
                        'required' => false,
                        'validate_callback' => [$this, 'validateDate']
                    ],
                    'donor' => [
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'sanitize_text_field',
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
                            'desc',
                        ],
                    ],
                    'locale' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => get_locale(),
                    ],
                    'mode' => [
                        'type' => 'boolean',
                        'required' => false,
                        'default' => false,
                    ],
                ],
            ]
        );
    }

    /**
     * @unreleased Change this to use the new ListTable class
     * @since      2.20.0
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     * @throws ColumnIdCollisionException
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $this->request = $request;
        $this->listTable = give(DonationsListTable::class);

        $donations = $this->getDonations();
        $donationsCount = $this->getTotalDonationsCount();
        $totalPages = (int)ceil($donationsCount / $this->request->get_param('perPage'));

        $this->listTable->items($donations, $this->request->get_param('locale'));

        return new WP_REST_Response(
            [
                'items' => $this->listTable->getItems(),
                'totalItems' => $donationsCount,
                'totalPages' => $totalPages
            ]
        );
    }

    /**
     * @unreleased Replace Query Builder with Donations model
     * @since 2.21.0
     *
     * @return array
     */
    public function getDonations(): array
    {
        $page = $this->request->get_param('page');
        $perPage = $this->request->get_param('perPage');
        $sortColumns = $this->listTable->getSortColumnById($this->request->get_param('sortColumn') ?: 'id');
        $sortDirection = $this->request->get_param('sortDirection') ?: 'desc';

        $query = give()->donations->prepareQuery();
        $query = $this->getWhereConditions($query);

        foreach ($sortColumns as $sortColumn) {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $query->limit($perPage)
            ->offset(($page - 1) * $perPage);

        $donations = $query->getAll();

        if (!$donations) {
            return [];
        }

        return $donations;
    }

    /**
     * @unreleased Replace Query Builder with Donations model
     * @since 2.21.0
     *
     * @return int
     */
    public function getTotalDonationsCount(): int
    {
        $query = give()->donations->prepareQuery();
        $query = $this->getWhereConditions($query);

        return $query->count();
    }

    /**
     * @unreleased Remove joins as it uses ModelQueryBuilder and change clauses to use attach_meta
     * @since 2.21.0
     *
     * @param ModelQueryBuilder $query
     *
     * @return ModelQueryBuilder
     */
    private function getWhereConditions(ModelQueryBuilder $query): ModelQueryBuilder
    {
        $search = $this->request->get_param('search');
        $start = $this->request->get_param('start');
        $end = $this->request->get_param('end');
        $form = $this->request->get_param('form');
        $donor = $this->request->get_param('donor');
        $mode = $this->request->get_param('mode');

        if ($search) {
            if (ctype_digit($search)) {
                $query->where('id', $search);
            } else if (strpos($search, '@') !== false) {
                $query
                    ->whereLike('give_donationmeta_attach_meta_email.meta_value', $search);
            } else {
                $query
                    ->whereLike('give_donationmeta_attach_meta_firstName.meta_value', $search)
                    ->orWhereLike('give_donationmeta_attach_meta_lastName.meta_value', $search);
            }
        }

        if ($donor) {
            if (ctype_digit($donor)) {
                $query
                    ->where('give_donationmeta_attach_meta_donorId.meta_value', $donor);
            } else {
                $query
                    ->whereLike('give_donationmeta_attach_meta_firstName.meta_value', $donor)
                    ->orWhereLike('give_donationmeta_attach_meta_lastName.meta_value', $donor);
            }
        }

        if ($form) {
            $query
                ->where('give_donationmeta_attach_meta_formId.meta_value', $form);
        }

        if ($mode) {
            $query
                ->where('give_donationmeta_attach_meta_mode.meta_value', 'test');
        } else {
            $query
                ->where('give_donationmeta_attach_meta_mode.meta_value', 'live');
        }

        if ($start && $end) {
            $query->whereBetween('post_date', $start, $end);
        } elseif ($start) {
            $query->where('post_date', $start, '>=');
        } elseif ($end) {
            $query->where('post_date', $end, '<=');
        }

        return $query;
    }
}
