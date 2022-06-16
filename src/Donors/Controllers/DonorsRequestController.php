<?php

namespace Give\Donors\Controllers;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;
use WP_REST_Request;

/**
 * Donors Request Controller class
 *
 * @since 2.21.0
 */
class DonorsRequestController
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
     * @since 2.21.0
     *
     * @return array
     */
    public function getDonors(): array
    {
        $page = $this->request->get_param('page');
        $perPage = $this->request->get_param('perPage');

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

        $query = $this->getWhereConditions($query);

        $query->limit($perPage);

        return $query->getAll();
    }

    /**
     * @since 2.21.0
     *
     * @return int
     */
    public function getTotalDonorsCount(): int
    {
        $query = DB::table('give_donors');
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
