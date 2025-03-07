<?php

namespace Give\Donations\Controllers;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationRoute;
use phpDocumentor\Reflection\Types\Boolean;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
 */
class DonationRequestController
{
    /**
     * @var Boolean
     */
    private $isAdmin;

    /**
     * @var Boolean
     */
    private $isAnonymousDonationsRedacted;

    /**
     * @var Boolean
     */
    private $isSensitiveDataIncluded;

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
        $this->isAdmin = current_user_can('manage_options');
        $this->isSensitiveDataIncluded = 'include' === $request->get_param('sensitiveData');

        if ( ! $this->isAdmin && $this->isSensitiveDataIncluded) {
            return new WP_REST_Response(
                ['message' => __('You do not have permission to include sensitive data.', 'give')],
                403
            );
        }

        $anonymousDonations = $request->get_param('anonymousDonations');
        $this->isAnonymousDonationsRedacted = 'redact' === $anonymousDonations;

        if ( ! $this->isAdmin && 'include' === $anonymousDonations) {
            return new WP_REST_Response(
                ['message' => __('You do not have permission to include anonymous donations.', 'give')],
                403
            );
        }

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

        if ('exclude' === $anonymousDonations) {
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
        $sensitiveDataToRemove = [];
        if ( ! $this->isSensitiveDataIncluded) {
            $sensitiveDataToRemove = [
                'donorIp',
                'email',
                'phone',
                'billingAddress',
            ];
        }

        $anonymousDataRedacted = [];
        if ($donation->anonymous && $this->isAnonymousDonationsRedacted) {
            $anonymousDataRedacted = [
                'donorId',
                'honorific',
                'firstName',
                'lastName',
                'company',
            ];
        }

        $donation = $donation->toArray();

        foreach ($sensitiveDataToRemove as $property) {
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
     * @unreleased
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
