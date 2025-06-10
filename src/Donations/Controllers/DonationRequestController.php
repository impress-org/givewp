<?php

namespace Give\Donations\Controllers;

use Give\API\REST\V3\Routes\Donations\ValueObjects\DonationAnonymousMode;
use Give\API\REST\V3\Routes\Donations\ValueObjects\DonationRoute;
use Give\Donations\Models\Donation;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 4.0.0
 */
class DonationRequestController
{
    /**
     * @since 4.0.0
     *
     * @return WP_Error|WP_REST_Response
     */
    public function getDonation(WP_REST_Request $request)
    {
        $donation = Donation::find($request->get_param('id'));
        $includeSensitiveData = $request->get_param('includeSensitiveData');
        $donationAnonymousMode = new DonationAnonymousMode($request->get_param('anonymousDonations'));

        if ( ! $donation || ($donation->anonymous && $donationAnonymousMode->isExcluded())) {
            return new WP_Error('donation_not_found', __('Donation not found', 'give'), ['status' => 404]);
        }

        return new WP_REST_Response($this->escDonation($donation, $includeSensitiveData, $donationAnonymousMode));
    }

    /**
     * @since 4.0.0
     */
    public function getDonations(WP_REST_Request $request): WP_REST_Response
    {

        $includeSensitiveData = $request->get_param('includeSensitiveData');

        $donationAnonymousMode = new DonationAnonymousMode($request->get_param('anonymousDonations'));

        $page = $request->get_param('page');
        $perPage = $request->get_param('per_page');
        $sortColumn = $this->getSortColumn($request->get_param('sort'));
        $sortDirection = $request->get_param('direction');
        $mode = $request->get_param('mode');

        $query = Donation::query();

        if ($campaignId = $request->get_param('campaignId')) {
            // Filter by CampaignId
            $query->where('give_donationmeta_attach_meta_campaignId.meta_value', $campaignId);
        }

        if ($donationAnonymousMode->isExcluded()) {
            // Exclude anonymous donations from results
            $query->where('give_donationmeta_attach_meta_anonymous.meta_value', 0);
        }

        // Include only current payment "mode"
        $query->where('give_donationmeta_attach_meta_mode.meta_value', $mode);

        // Include only valid statuses
        $query->whereIn('post_status', ['publish', 'give_subscription']);

        $query
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->orderBy($sortColumn, $sortDirection);

        $donations = $query->getAll() ?? [];
        $donations = array_map(function ($donation) use ($includeSensitiveData, $donationAnonymousMode) {
            return $this->escDonation($donation, $includeSensitiveData, $donationAnonymousMode);
        }, $donations);

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
     * @since 4.0.0
     */
    public function escDonation(
        Donation $donation,
        bool $includeSensitiveData = false,
        DonationAnonymousMode $donationAnonymousMode = null
    ): array
    {
        $sensitiveDataExcluded = [];
        if ( ! $includeSensitiveData) {
            $sensitiveDataExcluded = [
                'donorIp',
                'email',
                'phone',
                'billingAddress',
                'purchaseKey'
            ];
        }

        $anonymousDataRedacted = [];
        if ($donation->anonymous && $donationAnonymousMode->isRedacted()) {
            $anonymousDataRedacted = [
                'donorId',
                'honorific',
                'firstName',
                'lastName',
                'company'
            ];
        }

        $donation = $donation->toArray();

        foreach ($sensitiveDataExcluded as $property) {
            if (array_key_exists($property, $donation)) {
                unset($donation[$property]);
            }
        }

        foreach ($anonymousDataRedacted as $property) {
            if (array_key_exists($property, $donation)) {
                $donation[$property] = __('anonymous', 'give');
            }
        }

        return $donation;
    }

    /**
     * @since 4.0.0
     */
    public function getSortColumn(string $sortColumn): string
    {
        $sortColumnsMap = [
            'id' => 'ID',
            'createdAt' => 'post_date',
            'updatedAt' => 'post_modified',
            'status' => 'post_status',
            'amount' => 'give_donationmeta_attach_meta_amount.meta_value',
            'feeAmountRecovered' => 'give_donationmeta_attach_meta_feeAmountRecovered.meta_value',
            'donorId' => 'give_donationmeta_attach_meta_donorId.meta_value',
            'firstName' => 'give_donationmeta_attach_meta_firstName.meta_value',
            'lastName' => 'give_donationmeta_attach_meta_lastName.meta_value',
        ];

        return $sortColumnsMap[$sortColumn];
    }
}
