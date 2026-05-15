<?php

namespace Unit\API\REST\V3\Support;

use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorRoute;
use Give\API\REST\V3\Support\RouteAccess;
use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationNoteType;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Donors\Models\DonorNote;
use Give\Donors\ValueObjects\DonorNoteType;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Server;

/**
 * Covers SVUL-81: givewp/v3 donor, donation, subscription, note, and
 * statistics endpoints are public by default. Site owners can opt a route
 * into requiring authentication via the corresponding
 * 'givewp_rest_api_v3_{slug}_is_private' filter.
 *
 * @since 4.15.2
 */
class RouteAccessTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    private const SLUGS = [
        RouteAccess::DONORS,
        RouteAccess::DONATIONS,
        RouteAccess::SUBSCRIPTIONS,
        RouteAccess::DONOR_NOTES,
        RouteAccess::DONATION_NOTES,
        RouteAccess::DONOR_STATISTICS,
    ];

    public function tearDown(): void
    {
        foreach (self::SLUGS as $slug) {
            remove_all_filters("givewp_rest_api_v3_{$slug}_is_private");
        }
        parent::tearDown();
    }

    /**
     * @since 4.15.2
     */
    public function routeProvider(): array
    {
        return [
            'donors collection'         => [RouteAccess::DONORS, false],
            'single donor'              => [RouteAccess::DONORS, true],
            'donations collection'      => [RouteAccess::DONATIONS, false],
            'single donation'           => [RouteAccess::DONATIONS, true],
            'subscriptions collection'  => [RouteAccess::SUBSCRIPTIONS, false],
            'single subscription'       => [RouteAccess::SUBSCRIPTIONS, true],
            'donor notes collection'    => [RouteAccess::DONOR_NOTES, false],
            'single donor note'         => [RouteAccess::DONOR_NOTES, true],
            'donation notes collection' => [RouteAccess::DONATION_NOTES, false],
            'single donation note'      => [RouteAccess::DONATION_NOTES, true],
            'donor statistics'          => [RouteAccess::DONOR_STATISTICS, true],
        ];
    }

    /**
     * @since 4.15.2
     *
     * @dataProvider routeProvider
     */
    public function testAnonymousRequestsAreAllowedByDefault(string $filterSlug, bool $isSingle)
    {
        $url = $this->buildRoute($filterSlug, $isSingle);

        $request = $this->createRequest(WP_REST_Server::READABLE, $url);
        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());
    }

    /**
     * @since 4.15.2
     *
     * @dataProvider routeProvider
     */
    public function testFilterRestrictsAnonymousAccess(string $filterSlug, bool $isSingle)
    {
        $url = $this->buildRoute($filterSlug, $isSingle);

        add_filter("givewp_rest_api_v3_{$filterSlug}_is_private", '__return_true');

        $request = $this->createRequest(WP_REST_Server::READABLE, $url);
        $response = $this->dispatchRequest($request);

        $this->assertEquals(401, $response->get_status());
    }

    /**
     * @since 4.15.2
     *
     * @dataProvider routeProvider
     */
    public function testAdministratorCanAccessEvenWhenRoutePrivate(string $filterSlug, bool $isSingle)
    {
        $url = $this->buildRoute($filterSlug, $isSingle);

        add_filter("givewp_rest_api_v3_{$filterSlug}_is_private", '__return_true');

        $request = $this->createRequest(WP_REST_Server::READABLE, $url, [], 'administrator');
        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());
    }

    /**
     * Each filter is independent: opting one route into private does not
     * restrict any others.
     *
     * @since 4.15.2
     */
    public function testEnablingOneFilterDoesNotAffectOtherRoutes()
    {
        $restrictedUrl = $this->buildRoute(RouteAccess::DONORS, false);
        $openUrl = $this->buildRoute(RouteAccess::DONATIONS, false);

        add_filter('givewp_rest_api_v3_donors_is_private', '__return_true');

        $restrictedResponse = $this->dispatchRequest($this->createRequest(WP_REST_Server::READABLE, $restrictedUrl));
        $openResponse = $this->dispatchRequest($this->createRequest(WP_REST_Server::READABLE, $openUrl));

        $this->assertEquals(401, $restrictedResponse->get_status());
        $this->assertEquals(200, $openResponse->get_status());
    }

    /**
     * @since 4.15.2
     */
    private function buildRoute(string $filterSlug, bool $isSingle): string
    {
        $namespace = '/' . DonorRoute::NAMESPACE;

        switch ($filterSlug) {
            case RouteAccess::DONORS:
                $donor = Donor::factory()->create();

                return $isSingle ? "{$namespace}/donors/{$donor->id}" : "{$namespace}/donors";

            case RouteAccess::DONATIONS:
                $donation = Donation::factory()->create([
                    'status'    => DonationStatus::COMPLETE(),
                    'anonymous' => false,
                ]);

                return $isSingle ? "{$namespace}/donations/{$donation->id}" : "{$namespace}/donations";

            case RouteAccess::SUBSCRIPTIONS:
                $subscription = Subscription::factory()->create();

                return $isSingle
                    ? "{$namespace}/subscriptions/{$subscription->id}"
                    : "{$namespace}/subscriptions";

            case RouteAccess::DONOR_NOTES:
                $donor = Donor::factory()->create();
                $base = "{$namespace}/donors/{$donor->id}/notes";

                if (!$isSingle) {
                    return $base;
                }

                $note = DonorNote::create([
                    'donorId' => $donor->id,
                    'content' => 'RouteAccessTest donor note',
                    'type'    => DonorNoteType::ADMIN(),
                ]);

                return "{$base}/{$note->id}";

            case RouteAccess::DONATION_NOTES:
                $donation = Donation::factory()->create([
                    'status'    => DonationStatus::COMPLETE(),
                    'anonymous' => false,
                ]);
                $base = "{$namespace}/donations/{$donation->id}/notes";

                if (!$isSingle) {
                    return $base;
                }

                $note = DonationNote::create([
                    'donationId' => $donation->id,
                    'content'    => 'RouteAccessTest donation note',
                    'type'       => DonationNoteType::ADMIN(),
                ]);

                return "{$base}/{$note->id}";

            case RouteAccess::DONOR_STATISTICS:
                $donor = Donor::factory()->create();

                return "{$namespace}/donors/{$donor->id}/statistics";
        }

        throw new \InvalidArgumentException("Unknown filter slug: $filterSlug");
    }
}
