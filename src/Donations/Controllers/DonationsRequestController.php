<?php

namespace Give\Donations\Controllers;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;
use WP_REST_Request;

/**
 * Donations Request Controller class
 *
 * @since 2.21.0
 */
class DonationsRequestController
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
    public function getDonations(): array
    {
        $page = $this->request->get_param('page');
        $perPage = $this->request->get_param('perPage');

        $query = DB::table('posts')
            ->distinct()
            ->select(
                'id',
                ['post_date', 'createdAt'],
                ['post_status', 'status']
            )
            ->attachMeta(
                'give_donationmeta',
                'id',
                'donation_id',
                ...DonationMetaKeys::getColumnsForAttachMetaQuery()
            )
            ->where('post_type', 'give_payment');

        $query = $this->getWhereConditions($query);

        $query->limit($perPage)
            ->orderBy('id', 'DESC')
            ->offset(($page - 1) * $perPage);

        $donations = $query->getAll();

        if (!$donations) {
            return [];
        }

        return $donations;
    }

    /**
     * @since 2.21.0
     *
     * @return int
     */
    public function getTotalDonationsCount(): int
    {
        $query = DB::table('posts')
            ->where('post_type', 'give_payment');

        $query = $this->getWhereConditions($query);

        return $query->count();
    }

    /**
     * @param QueryBuilder $query
     * @since 2.21.0
     *
     * @return QueryBuilder
     */
    private function getWhereConditions(QueryBuilder $query): QueryBuilder
    {
        $search = $this->request->get_param('search');
        $start = $this->request->get_param('start');
        $end = $this->request->get_param('end');
        $form = $this->request->get_param('form');
        $donor = $this->request->get_param('donor');

        if ($form || $donor || ($search && !ctype_digit($search))) {
            $query->leftJoin(
                'give_donationmeta',
                'id',
                'metaTable.donation_id',
                'metaTable'
            );
        }

        if ($search) {
            if (ctype_digit($search)) {
                $query->where('id', $search);
            } else if (strpos($search, '@') !== false) {
                $query
                    ->where('metaTable.meta_key', DonationMetaKeys::EMAIL)
                    ->whereLike('metaTable.meta_value', $search);
            } else {
                $query
                    ->where('metaTable.meta_key', DonationMetaKeys::FIRST_NAME)
                    ->whereLike('metaTable.meta_value', $search)
                    ->orWhere('metaTable.meta_key', DonationMetaKeys::LAST_NAME)
                    ->whereLike('metaTable.meta_value', $search);
            }
        }

        if ($donor) {
            if (ctype_digit($donor)) {
                $query
                    ->where('metaTable.meta_key', DonationMetaKeys::DONOR_ID)
                    ->where('metaTable.meta_value', $donor);
            } else {
                $query
                    ->where('metaTable.meta_key', DonationMetaKeys::FIRST_NAME)
                    ->whereLike('metaTable.meta_value', $donor)
                    ->orWhere('metaTable.meta_key', DonationMetaKeys::LAST_NAME)
                    ->whereLike('metaTable.meta_value', $donor);
            }
        }

        if ($form) {
            $query
                ->where('metaTable.meta_key', DonationMetaKeys::FORM_ID)
                ->where('metaTable.meta_value', $form);
        }

        if ($start && $end) {
            $query->whereBetween('post_date', $start, $end);
        } else if ($start) {
            $query->where('post_date', $start, '>=');
        } else if ($end) {
            $query->where('post_date', $end, '<=');
        }

        return $query;
    }
}
