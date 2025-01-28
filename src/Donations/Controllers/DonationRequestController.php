<?php

namespace Give\Donations\Controllers;

use Give\Donations\Models\Donation;
use Give\Donations\Repositories\DonationRepository;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationRoute;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
 */
class DonationRequestController
{
    /**
     * @unreleased
     *
     * @return WP_Error | WP_REST_Response
     */
    public function getDonation(WP_REST_Request $request)
    {
        $donation = Donation::find($request->get_param('id'));

        if ( ! $donation) {
            return new WP_Error('donation_not_found', __('Donation not found', 'give'), ['status' => 404]);
        }

        return new WP_REST_Response($donation->toArray());
    }

    /**
     * @unreleased
     */
    public function getDonations(WP_REST_Request $request): WP_REST_Response
    {
        $campaignId = $request->get_param('campaignId');
        $page = $request->get_param('page');
        $perPage = $request->get_param('per_page');

        $query = give(DonationRepository::class)->prepareQuery();

        if ($campaignId) {
            $metaKey = DonationMetaKeys::CAMPAIGN_ID;
            $query->attachMeta('give_donationmeta', 'ID', 'donation_id', $metaKey)
                ->where("give_donationmeta_attach_meta_{$metaKey}.meta_value", $campaignId);
        }

        $query
            ->limit($perPage)
            ->offset(($page - 1) * $perPage);

        $donations = $query->getAll() ?? [];
        $totalDonations = empty($donations) ? 0 : Donation::query()->count();
        $totalPages = (int)ceil($totalDonations / $perPage);

        $response = rest_ensure_response($donations);
        $response->header('X-WP-Total', $totalDonations);
        $response->header('X-WP-TotalPages', $totalPages);

        $base = add_query_arg(
            map_deep($request->get_query_params(), function ($value) {
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }

                return urlencode($value);
            }),
            rest_url(DonationRoute::DONATIONS)
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
}
