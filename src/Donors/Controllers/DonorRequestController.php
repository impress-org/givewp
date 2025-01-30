<?php

namespace Give\Donors\Controllers;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donors\Models\Donor;
use Give\Donors\Repositories\DonorRepository;
use Give\Donors\ValueObjects\DonorRoute;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Framework\QueryBuilder\QueryBuilder;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
 */
class DonorRequestController
{
    /**
     * @unreleased
     *
     * @return WP_Error | WP_REST_Response
     */
    public function getDonor(WP_REST_Request $request)
    {
        $donor = Donor::find($request->get_param('id'));

        if ( ! $donor) {
            return new WP_Error('donor_not_found', __('Donor not found', 'give'), ['status' => 404]);
        }

        return new WP_REST_Response($this->escDonor($donor));
    }

    /**
     * @unreleased
     */
    public function getDonors(WP_REST_Request $request): WP_REST_Response
    {
        $page = $request->get_param('page');
        $perPage = $request->get_param('per_page');

        $query = give(DonorRepository::class)->prepareQuery();

        if ($campaignId = $request->get_param('campaignId')) {
            $query->select(['donationmeta1.donation_id', 'donationId'])
                ->distinct()
                ->join(function (JoinQueryBuilder $builder) use ($campaignId) {
                    $builder->innerJoin('give_donationmeta', 'donationmeta1')
                        ->joinRaw("ON donationmeta1.meta_key = '" . DonationMetaKeys::DONOR_ID . "' AND donationmeta1.meta_value = ID");

                    $builder->innerJoin('give_donationmeta', 'donationmeta2')
                        ->joinRaw("ON donationmeta2.meta_key = '" . DonationMetaKeys::CAMPAIGN_ID . "' AND donationmeta2.meta_value = {$campaignId}");
                })->whereIn('donationmeta1.donation_id', function (QueryBuilder $builder) {
                    $builder
                        ->select('ID')
                        ->from('posts')
                        ->whereRaw("WHERE ID = donationmeta1.donation_id AND post_type = 'give_payment' AND post_status = 'publish'");
                });
        }

        $query
            ->limit($perPage)
            ->offset(($page - 1) * $perPage);

        $donors = $query->getAll() ?? [];
        $donors = array_map([$this, 'escDonor'], $donors);
        $totalDonors = empty($donors) ? 0 : Donor::query()->count();
        $totalPages = (int)ceil($totalDonors / $perPage);

        $response = rest_ensure_response($donors);
        $response->header('X-WP-Total', $totalDonors);
        $response->header('X-WP-TotalPages', $totalPages);

        $base = add_query_arg(
            map_deep($request->get_query_params(), function ($value) {
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }

                return urlencode($value);
            }),
            rest_url(DonorRoute::DONORS)
        );

        if ($page > 1) {
            $prevPage = $page - 1;

            if ($prevPage > $totalPages) {
                $prevPage = $totalPages;
            }

            $response->link_header('prev', add_query_arg('page', $prevPage, $base));
        }

        if ($totalPages > $page) {
            $nextPage = $page + 1;
            $response->link_header('next', add_query_arg('page', $nextPage, $base));
        }

        return $response;
    }

    /**
     * @unreleased
     */
    public function escDonor(Donor $donor): array
    {
        $donor = $donor->toArray();

        if ( ! current_user_can('manage_options')) {
            $sensitiveProperties = [
                'userId',
                'email',
                'phone',
                'additionalEmails',
            ];

            foreach ($sensitiveProperties as $property) {
                if (array_key_exists($property, $donor)) {
                    unset($donor[$property]);
                }
            }
        }

        return $donor;
    }
}
