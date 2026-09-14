<?php

namespace Give\Tests\Feature\Donors;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Donor_Wall;

/**
 * The donor wall reads raw rows out of the donationmeta table, and hardly any of them are
 * serialized; a first name is stored as the plain string it was typed as. So the read has to
 * return those untouched while still refusing to hand back an object, which is the pair of
 * requirements these tests hold together.
 *
 * @since TBD
 */
class DonorWallTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testRendersDonorNameStoredAsPlainString()
    {
        $donation = $this->createDonorWallDonation();

        $html = $this->renderDonorWall(['show_avatar' => 'false']);

        $this->assertStringContainsString(
            esc_html(trim($donation->firstName . ' ' . $donation->lastName)),
            $html
        );
    }

    /**
     * @since TBD
     */
    public function testRendersDonorInitialsWhenGravatarIsUnavailable()
    {
        $donation = $this->createDonorWallDonation();
        $this->denyGravatarLookups();

        $html = $this->renderDonorWall(['show_avatar' => 'true']);

        $expectedInitials = give_get_name_initial(
            [
                'firstname' => $donation->firstName,
                'lastname' => $donation->lastName,
            ]
        );

        $this->assertStringContainsString(
            "<span class='give-donor-container__image__name_initial'>" . esc_html($expectedInitials) . '</span>',
            $html
        );
    }

    /**
     * @since TBD
     */
    public function testDoesNotRenderSerializedObjectPayloads()
    {
        global $wpdb;

        $marker = 'givewp-donor-wall-object-payload';
        $donation = $this->createDonorWallDonation();

        /*
         * Written straight to the table because give_update_meta() runs the value through
         * maybe_serialize(), which wraps an already-serialized string in a second layer and so
         * never reproduces the row an injected payload actually leaves behind.
         */
        $wpdb->update(
            $wpdb->donationmeta,
            ['meta_value' => serialize((object)['name' => $marker])],
            ['donation_id' => $donation->id, 'meta_key' => DonationMetaKeys::LAST_NAME]
        );

        $html = $this->renderDonorWall(['show_avatar' => 'false']);

        $this->assertStringNotContainsString($marker, $html);
        $this->assertStringNotContainsString('stdClass', $html);
    }

    /**
     * The wall queries published donations only, renders the name only when the donation is not
     * anonymous, and skips the initials entirely when the donor has an uploaded avatar. The
     * factories randomize each of those.
     *
     * @since TBD
     */
    private function createDonorWallDonation(): Donation
    {
        return Donation::factory()->create(
            [
                'status' => DonationStatus::COMPLETE(),
                'anonymous' => false,
                'donorId' => Donor::factory()->create(['avatarId' => 0])->id,
            ]
        );
    }

    /**
     * @since TBD
     *
     * @param array<string, string> $atts Shortcode attributes to override.
     *
     * @return string The rendered donor wall markup.
     */
    private function renderDonorWall(array $atts): string
    {
        return Give_Donor_Wall::get_instance()->render_shortcode($atts);
    }

    /**
     * give_validate_gravatar() calls gravatar.com over HTTP and falls back to the name initials
     * when the avatar is missing. Answering that request with a 404 pins the test to that fallback
     * without reaching the network. Every other URL is left alone, so a request the render path
     * should not be making still surfaces instead of being quietly answered.
     *
     * @since TBD
     */
    private function denyGravatarLookups(): void
    {
        add_filter(
            'pre_http_request',
            static function ($preempt, $parsedArgs, $url) {
                if (strpos($url, 'gravatar.com') === false) {
                    return $preempt;
                }

                return ['response' => ['code' => 404, 'message' => 'Not Found'], 'body' => ''];
            },
            10,
            3
        );
    }
}
