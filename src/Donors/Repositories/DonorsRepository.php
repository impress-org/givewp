<?php

namespace Give\Donors\Repositories;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;
use WP_REST_Request;

class DonorsRepository
{
    /**
     * @param WP_REST_Request $request
     * @unreleased
     *
     * @return array
     */
    public function getDonorsForRequest(WP_REST_Request $request)
    {
        $page = $request->get_param('page');
        $perPage = $request->get_param('perPage');

        $query = DB::table('give_donors')
            ->select(
                'id',
                ['user_id', 'userId'],
                'email',
                'name',
                ['purchase_value', 'donationRevenue'],
                ['purchase_count', 'donationCount'],
                ['payment_ids', 'paymentIds'],
                ['date_created', 'createdAt']
            )
            ->attachMeta(
                'give_donormeta',
                'id',
                'donor_id',
                ['_give_donor_title_prefix', 'titlePrefix']
            )
            ->limit($perPage)
            ->orderBy('id', 'DESC')
            ->offset(($page - 1) * $perPage);

        $query = $this->getWhereConditionsForRequest($query, $request);

        $query->limit($perPage);

        return $query->getAll();
    }

    /**
     * @param WP_REST_Request $request
     * @unreleased
     *
     * @return int
     */
    public function getTotalDonorsCountForRequest(WP_REST_Request $request)
    {
        $query = DB::table('give_donors');
        $query = $this->getWhereConditionsForRequest($query, $request);

        return $query->count();
    }

    /**
     * @unreleased
     * @return int
     */
    public function getDonorsCount()
    {
        return DB::table('give_donors')->count();
    }

    /**
     * @param QueryBuilder $builder
     * @param WP_REST_Request $request
     * @unreleased
     *
     * @return QueryBuilder
     */
    private function getWhereConditionsForRequest(QueryBuilder $builder, WP_REST_Request $request)
    {

        $search = $request->get_param('search');
        $start = $request->get_param('start');
        $end = $request->get_param('end');
        $donations = $request->get_param('donations');

        if ($donations !== 0) {
            $builder->whereLike('payment_ids', $donations);
        }

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

        return $builder;
    }
}
