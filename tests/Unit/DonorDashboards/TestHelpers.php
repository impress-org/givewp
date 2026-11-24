<?php

namespace Give\Tests\Unit\DonorDashboards;

use Exception;
use Give\DonorDashboards\Helpers;
use Give\Donors\Models\Donor;
use Give\Donors\ValueObjects\DonorMetaKeys;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class TestHelpers extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.20.0
     */
    public function setUp(): void
    {
        parent::setUp();

        give_update_option('email_access', 'enabled');
    }

    /**
     * @since 4.20.0
     */
    public function tearDown(): void
    {
        unset($_GET['give_nl']);
        wp_set_current_user(0);
        give_delete_option('email_access');

        parent::tearDown();
    }

    /**
     * @since 4.20.0
     *
     * @throws Exception
     */
    public function testShouldLinkDonorWhenTokenEmailIsTheAccountEmail(): void
    {
        $user = $this->factory()->user->create_and_get();
        $donor = Donor::factory()->create(['userId' => null, 'email' => $user->user_email]);
        $this->requestWithToken($donor, $user->ID);

        $donorId = Helpers::getCurrentDonorId();

        $this->assertSame($donor->id, $donorId);
        $this->assertSame($user->ID, Donor::find($donor->id)->userId);
    }

    /**
     * @since 4.20.0
     * @dataProvider missingNameMetadata
     *
     * @throws Exception
     */
    public function testShouldLinkLegacyDonorWithMissingNameMetadata(string $metaKey, string $property): void
    {
        $user = $this->factory()->user->create_and_get();
        $donor = Donor::factory()->create(['userId' => null, 'email' => $user->user_email]);
        give()->donor_meta->delete_meta($donor->id, $metaKey);
        $this->assertNull(Donor::find($donor->id)->$property);
        $this->requestWithToken($donor, $user->ID);

        $this->assertSame($donor->id, Helpers::getCurrentDonorId());
        $this->assertSame($user->ID, Donor::find($donor->id)->userId);
        $this->assertSame('', give()->donor_meta->get_meta($donor->id, $metaKey, true));

        unset($_GET['give_nl']);
        $this->assertEquals($donor->id, Helpers::getCurrentDonorId());
    }

    /**
     * @since 4.20.0
     */
    public function missingNameMetadata(): array
    {
        return [
            'missing first name' => [DonorMetaKeys::FIRST_NAME, 'firstName'],
            'missing last name' => [DonorMetaKeys::LAST_NAME, 'lastName'],
        ];
    }

    /**
     * @since 4.20.0
     *
     * @throws Exception
     */
    public function testShouldNotLinkDonorWhenTokenEmailIsNotTheAccountEmail(): void
    {
        $user = $this->factory()->user->create_and_get();
        $donor = Donor::factory()->create(['userId' => null]);
        $this->requestWithToken($donor, $user->ID);

        $this->assertEquals($donor->id, Helpers::getCurrentDonorId());

        $this->assertEmpty(Donor::find($donor->id)->userId);
    }

    /**
     * @since 4.20.0
     *
     * @throws Exception
     */
    public function testShouldNotLinkDonorWithoutToken(): void
    {
        $user = $this->factory()->user->create_and_get();
        $donor = Donor::factory()->create(['userId' => null, 'email' => $user->user_email]);
        wp_set_current_user($user->ID);

        $this->assertNull(Helpers::getCurrentDonorId());
        $this->assertEmpty(Donor::find($donor->id)->userId);
    }

    /**
     * @since 4.20.0
     *
     * @throws Exception
     */
    public function testShouldNotLinkDonorWithAnInvalidToken(): void
    {
        $user = $this->factory()->user->create_and_get();
        $donor = Donor::factory()->create(['userId' => null, 'email' => $user->user_email]);
        wp_set_current_user($user->ID);
        $_GET['give_nl'] = wp_generate_password(32, false);

        $this->assertNull(Helpers::getCurrentDonorId());
        $this->assertEmpty(Donor::find($donor->id)->userId);
    }

    /**
     * @since 4.20.0
     *
     * @throws Exception
     */
    public function testShouldNotReassignDonorLinkedToAnotherUser(): void
    {
        $user = $this->factory()->user->create_and_get();
        $owner = $this->factory()->user->create_and_get();
        $donor = Donor::factory()->create(['userId' => $owner->ID, 'email' => $user->user_email]);
        $this->requestWithToken($donor, $user->ID);

        Helpers::getCurrentDonorId();

        $this->assertSame($owner->ID, Donor::find($donor->id)->userId);
    }

    /**
     * @since 4.20.0
     *
     * @throws Exception
     */
    public function testShouldNotLinkDonorWhenEmailAccessIsDisabled(): void
    {
        give_update_option('email_access', 'disabled');
        $user = $this->factory()->user->create_and_get();
        $donor = Donor::factory()->create(['userId' => null, 'email' => $user->user_email]);
        $this->requestWithToken($donor, $user->ID);

        Helpers::getCurrentDonorId();

        $this->assertEmpty(Donor::find($donor->id)->userId);
    }

    /**
     * Logs the user in and arrives with a fresh emailed access token for the donor.
     *
     * @since 4.20.0
     */
    private function requestWithToken(Donor $donor, int $userId): void
    {
        $token = wp_generate_password(32, false);

        give()->email_access->set_verify_key($donor->id, $donor->email, $token);
        wp_set_current_user($userId);
        $_GET['give_nl'] = $token;
    }
}
