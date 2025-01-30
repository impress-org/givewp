<?php

namespace Give\Donations\Controllers;

use Give\Donations\Models\Donation;
use Give\Donations\Repositories\DonationRepository;
use Give\Donations\ValueObjects\DonationRoute;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
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

        return new WP_REST_Response($this->escDonation($donation));
    }

    /**
     * @unreleased
     */
    public function getDonations(WP_REST_Request $request): WP_REST_Response
    {
        $page = $request->get_param('page');
        $perPage = $request->get_param('per_page');

        $query = give(DonationRepository::class)->prepareQuery();

        if ($campaignId = $request->get_param('campaignId')) {
            // Filter by CampaignId
            $query->distinct()->join(function (JoinQueryBuilder $builder) use ($campaignId) {
                $builder->innerJoin('give_campaign_forms', 'campaign_forms')
                    ->joinRaw("ON campaign_forms.form_id = give_donationmeta_attach_meta_formId.meta_value AND campaign_forms.campaign_id = {$campaignId}");
            });
        }

        // Include only current payment "mode"
        $query->where('give_donationmeta_attach_meta_mode.meta_value', give_is_test_mode() ? 'test' : 'live');

        // Include only valid statuses
        $query->whereIn('post_status', ['publish', 'give_subscription']);

        $query
            ->limit($perPage)
            ->offset(($page - 1) * $perPage);

        $donations = $query->getAll() ?? [];
        $donations = array_map([$this, 'escDonation'], $donations);
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

    /**
     * @unreleased
     */
    public function escDonation(Donation $donation): array
    {
        $donation = $donation->toArray();

        if ( ! current_user_can('manage_options')) {
            $sensitiveProperties = [
                'donorIp',
                'email',
                'phone',
                'billingAddress',
            ];

            foreach ($sensitiveProperties as $property) {
                if (array_key_exists($property, $donation)) {
                    unset($donation[$property]);
                }
            }
        }

        return $donation;
    }
}
