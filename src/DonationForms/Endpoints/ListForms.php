<?php

namespace Give\DonationForms\Endpoints;

use Give\DonationForms\DataTransferObjects\DonationFormsResponseData;
use Give\DonationForms\ValueObjects\DonationFormMetaKeys;
use Give\Framework\Database\DB;
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
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $data = [];
        $this->request = $request;
        $forms = $this->getForms();
        $totalForms = $this->getTotalFormsCount();
        $totalPages = (int)ceil($totalForms / $this->request->get_param('perPage'));

        foreach ($forms as $form) {
            $data[] = DonationFormsResponseData::fromObject($form)->toArray();
        }

        return new WP_REST_Response(
            [
                'items' => $data,
                'totalItems' => $totalForms,
                'totalPages' => $totalPages,
                'trash' => defined('EMPTY_TRASH_DAYS') && EMPTY_TRASH_DAYS > 0,
            ]
        );
    }

    /**
     * @return array
     */
    public function getForms(): array
    {
        $page = $this->request->get_param('page');
        $perPage = $this->request->get_param('perPage');
        $search = $this->request->get_param('search');
        $status = $this->request->get_param('status');

        $query = DB::table('posts')
            ->select(
                'id',
                ['post_date', 'createdAt'],
                ['post_date_gmt', 'createdAtGmt'],
                ['post_status', 'status'],
                ['post_title', 'title']
            )
            ->attachMeta('give_formmeta', 'id', 'form_id',
                [DonationFormMetaKeys::FORM_EARNINGS, 'revenue'],
                [DonationFormMetaKeys::DONATION_LEVELS, 'donationLevels'],
                [DonationFormMetaKeys::SET_PRICE, 'setPrice'],
                [DonationFormMetaKeys::GOAL_OPTION, 'goalEnabled']
            )
            ->where('post_type', 'give_forms')
            ->limit($perPage)
            ->orderBy('id', 'DESC')
            ->offset(($page - 1) * $perPage);

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

        return $query->getAll();
    }

    /**
     * @return int
     */
    public function getTotalFormsCount(): int
    {
        $search = $this->request->get_param('search');
        $status = $this->request->get_param('status');
        $perPage = $this->request->get_param('perPage');

        $query = DB::table('posts')
            ->where('post_type', 'give_forms');

        if ($status === 'any') {
            $query->whereIn('post_status', ['publish', 'draft', 'pending']);
        } else {
            $query->where('post_status', $status);
        }

        if ($search) {
            if (ctype_digit($search)) {
                $query->where('ID', $search);
            } else {
                $query->whereLike('post_title', $search);
            }
        }

        $query->limit($perPage);

        return $query->count();
    }
}
