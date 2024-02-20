<?php

namespace Give\Donations\Endpoints;

use Give\Donations\ListTable\DonationsListTable;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationMode;
use Give\Framework\Database\DB;
use Give\Framework\ListTable\Exceptions\ColumnIdCollisionException;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Framework\QueryBuilder\Types\Operator;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 3.4.0 The class is extendable
 */
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
     * @since 3.4.0
     * @access public
     */
    public function __construct(DonationsListTable $listTable)
    {
        $this->listTable = $listTable;
    }

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
                    'testMode' => [
                        'type' => 'boolean',
                        'required' => false,
                        'default' => give_is_test_mode(),
                    ],
                    'return' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'columns',
                        'enum' => [
                            'model',
                            'columns',
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * @since 2.24.0 Change this to use the new ListTable class
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

        $donations = $this->getDonations();
        $donationsCount = $this->getTotalDonationsCount();
        $totalPages = (int)ceil($donationsCount / $this->request->get_param('perPage'));

        if ('model' === $this->request->get_param('return')) {
            $items = $donations;
        } else {
            $this->listTable->items($donations, $this->request->get_param('locale') ?? '');
            $items = $this->listTable->getItems();
        }

        return new WP_REST_Response(
            [
                'items' => $items,
                'totalItems' => $donationsCount,
                'totalPages' => $totalPages,
            ]
        );
    }

    /**
     * @since 2.24.0 Replace Query Builder with Donations model
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
        list($query) = $this->getWhereConditions($query);

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
     * @since 2.24.0 Replace Query Builder with Donations model
     * @since 2.21.0
     *
     * @return int
     */
    public function getTotalDonationsCount(): int
    {
        $query = DB::table('posts')
            ->where('post_type', 'give_payment')
            ->groupBy('mode');

        list($query, $dependencies) = $this->getWhereConditions($query);

        $query->attachMeta(
            'give_donationmeta',
            'ID',
            'donation_id',
            ...DonationMetaKeys::getColumnsForAttachMetaQueryFromArray($dependencies)
        );

        return $query->count();
    }

    /**
     * @since 3.4.0 Make this method protected so it can be extended
     * @since 3.2.0 Updated query to account for possible null and empty values for _give_payment_mode meta
     * @since 2.24.0 Remove joins as it uses ModelQueryBuilder and change clauses to use attach_meta
     * @since      2.21.0
     *
     * @param QueryBuilder $query
     *
     * @return array{0: QueryBuilder, 1: array<DonationMetaKeys>}
     */
    protected function getWhereConditions(QueryBuilder $query): array
    {
        $search = $this->request->get_param('search');
        $start = $this->request->get_param('start');
        $end = $this->request->get_param('end');
        $form = $this->request->get_param('form');
        $donor = $this->request->get_param('donor');
        $testMode = $this->request->get_param('testMode');

        $dependencies = [
            DonationMetaKeys::MODE(),
        ];

        $hasWhereConditions = $search || $start || $end || $form || $donor;

        if ($search) {
            if (ctype_digit($search)) {
                $query->where('id', $search);
            } elseif (strpos($search, '@') !== false) {
                $query
                    ->whereLike('give_donationmeta_attach_meta_email.meta_value', $search);
                $dependencies[] = DonationMetaKeys::EMAIL();
            } else {
                $query
                    ->whereLike('give_donationmeta_attach_meta_firstName.meta_value', $search)
                    ->orWhereLike('give_donationmeta_attach_meta_lastName.meta_value', $search);
                $dependencies[] = DonationMetaKeys::FIRST_NAME();
                $dependencies[] = DonationMetaKeys::LAST_NAME();
            }
        }

        if ($donor) {
            if (ctype_digit($donor)) {
                $query
                    ->where('give_donationmeta_attach_meta_donorId.meta_value', $donor);
                $dependencies[] = DonationMetaKeys::DONOR_ID();
            } else {
                $query
                    ->whereLike('give_donationmeta_attach_meta_firstName.meta_value', $donor)
                    ->orWhereLike('give_donationmeta_attach_meta_lastName.meta_value', $donor);
                $dependencies[] = DonationMetaKeys::FIRST_NAME();
                $dependencies[] = DonationMetaKeys::LAST_NAME();
            }
        }

        if ($form) {
            $query
                ->where('give_donationmeta_attach_meta_formId.meta_value', $form);
            $dependencies[] = DonationMetaKeys::FORM_ID();
        }

        if ($start && $end) {
            $query->whereBetween('post_date', $start, $end);
        } elseif ($start) {
            $query->where('post_date', $start, '>=');
        } elseif ($end) {
            $query->where('post_date', $end, '<=');
        }

        if ($hasWhereConditions) {
           $query->havingRaw('HAVING COALESCE(give_donationmeta_attach_meta_mode.meta_value, %s) = %s', DonationMode::LIVE, $testMode ? DonationMode::TEST : DonationMode::LIVE);
        } elseif ($testMode) {
            $query->where('give_donationmeta_attach_meta_mode.meta_value', DonationMode::TEST);
        } else {
            $query->whereIsNull('give_donationmeta_attach_meta_mode.meta_value')
            ->orWhere('give_donationmeta_attach_meta_mode.meta_value', DonationMode::TEST, '<>');
        }

        return [
            $query,
            $dependencies,
        ];
    }
}
