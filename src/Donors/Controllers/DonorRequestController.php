<?php

namespace Give\Donors\Controllers;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donors\Models\Donor;
use Give\Donors\ValueObjects\DonorAnonymousMode;
use Give\Donors\ValueObjects\DonorRoute;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Framework\QueryBuilder\QueryBuilder;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 4.0.0
 */
class DonorRequestController
{
    /**
     * @since 4.0.0
     *
     * @return WP_Error|WP_REST_Response
     */
    public function getDonor(WP_REST_Request $request)
    {
        $donor = Donor::find($request->get_param('id'));
        $includeSensitiveData = $request->get_param('includeSensitiveData');
        $donorAnonymousMode = new DonorAnonymousMode($request->get_param('anonymousDonors'));

        if ( ! $donor || ($this->isAnonymousDonor($donor) && $donorAnonymousMode->isExcluded())) {
            return new WP_Error('donor_not_found', __('Donor not found', 'give'), ['status' => 404]);
        }

        return new WP_REST_Response($this->escDonor($donor, $includeSensitiveData, $donorAnonymousMode));
    }

    /**
     * @since 4.0.0
     */
    public function getDonors(WP_REST_Request $request): WP_REST_Response
    {
        $page = $request->get_param('page');
        $perPage = $request->get_param('per_page');
        $sortColumn = $this->getSortColumn($request->get_param('sort'));
        $sortDirection = $request->get_param('direction');
        $mode = $request->get_param('mode');
        $includeSensitiveData = $request->get_param('includeSensitiveData');
        $donorAnonymousMode = new DonorAnonymousMode($request->get_param('anonymousDonors'));


        $query = Donor::query();

        // Donors only can be donors if they have donations associated with them
        if ($request->get_param('onlyWithDonations')) {
            $query->join(function (JoinQueryBuilder $builder) use ($mode) {
                // The donationmeta1.donation_id should be used in other "donationmeta" joins to make sure we are retrieving data from the proper donation
                $builder->innerJoin('give_donationmeta', 'donationmeta1')
                    ->joinRaw("ON donationmeta1.meta_key = '" . DonationMetaKeys::DONOR_ID . "' AND donationmeta1.meta_value = ID");

                // Include only current payment "mode"
                $builder->innerJoin('give_donationmeta', 'donationmeta2')
                    ->joinRaw("ON donationmeta2.meta_key = '" . DonationMetaKeys::MODE . "' AND donationmeta2.meta_value = '{$mode}' AND donationmeta2.donation_id = donationmeta1.donation_id");
            });


            if ($campaignId = $request->get_param('campaignId')) {
                // Filter by CampaignId - Donors only can be filtered by campaignId if they donated to a campaign
                $query->join(function (JoinQueryBuilder $builder) use ($campaignId) {
                    $builder->innerJoin('give_donationmeta', 'donationmeta3')
                        ->joinRaw("ON donationmeta3.meta_key = '" . DonationMetaKeys::CAMPAIGN_ID . "' AND donationmeta3.meta_value = {$campaignId} AND donationmeta3.donation_id = donationmeta1.donation_id");
                });
            }

            if ($donorAnonymousMode->isExcluded()) {
                // Exclude anonymous donors from results - Donors only can be excluded if they made an anonymous donation
                $query->join(function (JoinQueryBuilder $builder) {
                        $builder->innerJoin('give_donationmeta', 'donationmeta4')
                            ->joinRaw("ON donationmeta4.meta_key = '" . DonationMetaKeys::ANONYMOUS . "' AND donationmeta4.meta_value = 0 AND donationmeta4.donation_id = donationmeta1.donation_id");
                    });
            }

            // Make sure the donation is valid
            $query->whereIn('donationmeta1.donation_id', function (QueryBuilder $builder) {
                $builder
                    ->select('ID')
                    ->from('posts')
                    ->where('post_type', 'give_payment')
                    ->whereIn('post_status', ['publish', 'give_subscription'])
                    ->whereRaw("AND ID = donationmeta1.donation_id");
            });
        }

        $query
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->orderBy($sortColumn, $sortDirection);

        $donors = $query->getAll() ?? [];
        $donors = array_map(function ($donor) use ($includeSensitiveData, $donorAnonymousMode) {
            return $this->escDonor($donor, $includeSensitiveData, $donorAnonymousMode);
        }, $donors);

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
     * @since 4.0.0
     */
    public function escDonor(
        Donor $donor,
        bool $includeSensitiveData = false,
        DonorAnonymousMode $donationAnonymousMode = null
    ): array
    {
        $sensitiveDataExcluded = [];
        if ( ! $includeSensitiveData) {
            $sensitiveDataExcluded = [
                'userId',
                'email',
                'phone',
                'additionalEmails',
            ];
        }

        $anonymousDataRedacted = [];
        if ( ! is_null($donationAnonymousMode) && $donationAnonymousMode->isRedacted() && $this->isAnonymousDonor($donor)) {
            $anonymousDataRedacted = [
                'id',
                'name',
                'firstName',
                'lastName',
                'prefix',
            ];
        }

        $donor = $donor->toArray();

        foreach ($sensitiveDataExcluded as $property) {
            if (array_key_exists($property, $donor)) {
                unset($donor[$property]);
            }
        }

        foreach ($anonymousDataRedacted as $property) {
            if (array_key_exists($property, $donor)) {
                $donor[$property] = 'id' === $property ? 0 : __('anonymous', 'give');
            }
        }

        return $donor;
    }

    /**
     * @since 4.0.0
     */
    public function getSortColumn(string $sortColumn): string
    {
        $sortColumnsMap = [
            'id' => 'id',
            'createdAt' => 'date_created',
            'name' => 'name',
            'firstName' => 'give_donormeta_attach_meta_firstName.meta_value',
            'lastName' => 'give_donormeta_attach_meta_lastName.meta_value',
            'totalAmountDonated' => 'purchase_value',
            'totalNumberOfDonations' => 'purchase_count',
        ];

        return $sortColumnsMap[$sortColumn];
    }

    /**
     * @since 4.0.0
     */
    private function isAnonymousDonor(Donor $donor): bool
    {
        $isAnonymousDonor = false;

        if ($donor->donations) {
            foreach ($donor->donations as $donation) {
                if ($donation->anonymous) {
                    $isAnonymousDonor = true;
                    break;
                }
            }
        }

        return $isAnonymousDonor;
    }
}
