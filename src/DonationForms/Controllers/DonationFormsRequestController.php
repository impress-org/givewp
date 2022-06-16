<?php

namespace Give\DonationForms\Controllers;

use Give\DonationForms\ValueObjects\DonationFormMetaKeys;
use Give\Framework\Database\DB;
use WP_REST_Request;

/**
 * Donation Forms Request Controller class
 *
 * @since 2.21.0
 */
class DonationFormsRequestController
{
    /**
     * @var WP_REST_Request
     */
    private $request;

    /**
     * @param WP_REST_Request $request
     */
    public function __construct(WP_REST_Request $request)
    {
        $this->request = $request;
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
