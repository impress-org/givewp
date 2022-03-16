<?php

namespace Give\DonationForms\Repositories;

use Give\Framework\Database\DB;
use WP_REST_Request;

/**
 * @since 2.19.0
 */
class DonationFormsRepository
{
    /**
     * @param  WP_REST_Request  $request
     *
     * @return array
     */
    public function getFormsForRequest(WP_REST_Request $request)
    {
        $page    = $request->get_param('page');
        $perPage = $request->get_param('perPage');
        $search  = $request->get_param('search');
        $status  = $request->get_param('status');

        $query = DB::table('posts')
                   ->select(
                       ['ID', 'id'],
                       ['post_date', 'createdAt'],
                       ['post_date_gmt', 'createdAtGmt'],
                       ['post_status', 'status'],
                       ['post_title', 'title']
                   )
                   ->attachMeta('give_formmeta', 'id', 'form_id',
                       ['_give_form_earnings', 'revenue'],
                       ['_give_donation_levels', 'donationLevels'],
                       ['_give_set_price', 'setPrice'],
                       ['_give_goal_option', 'goalEnabled']
                   )
                   ->where('post_type', 'give_forms')
                   ->limit($perPage)
                   ->orderBy('id', 'DESC')
                   ->offset(($page - 1) * $perPage);

        // Status
        if ($status === 'any') {
            $query->whereIn('post_status', ['publish', 'draft', 'pending']);
        } else {
            $query->where('post_status', $status);
        }

        // Search
        if ($search) {
            if (ctype_digit($search)) {
                $query->where('ID', $search);
            } else {
                $searchTerms = array_map('trim', explode(' ', $search));
                foreach ($searchTerms as $term)
                {
                    if ($term)
                    {
                        $query->whereLike('post_title', $term);
                    }
                }
            }
        }

        return $query->getAll();
    }

    /**
     * @param  WP_REST_Request  $request
     *
     * @return int
     */
    public function getTotalFormsCountForRequest(WP_REST_Request $request)
    {
        $search  = $request->get_param('search');
        $status  = $request->get_param('status');
        $perPage = $request->get_param('perPage');

        $query = DB::table('posts')
                   ->selectRaw('SELECT COUNT(ID) AS count')
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

        $total = $query->get();

        return $total->count;
    }

    /**
     * @param  int  $formId
     *
     * @return int
     */
    public function getFormDonationsCount($formId)
    {
        $donations = DB::table('posts')
                       ->selectRaw('SELECT COUNT(ID) as count')
                       ->leftJoin('give_donationmeta', 'ID', 'donation_id')
                       ->where('meta_key', '_give_payment_form_id')
                       ->where('meta_value', $formId)
                       ->get();


        return $donations->count;
    }
}
