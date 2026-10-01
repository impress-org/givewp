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
 * The donor wall renders the donationmeta rows exactly as they are stored, so what it displays is
 * text and add-ons that read the template's meta keys keep working. Nothing on the path decodes a
 * stored value, which is the pair of requirements these tests hold together.
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
    public function testRendersSerializedPayloadsAsEscapedTextWithoutDecodingThem()
    {
        global $wpdb;

        $storedMetaValue = serialize((object)['name' => 'givewp-donor-wall-object-payload']);
        $donation = $this->createDonorWallDonation();

        /*
         * Written straight to the table because give_update_meta() runs the value through
         * maybe_serialize(), which wraps an already-serialized string in a second layer and so
         * never reproduces the row an injected payload actually leaves behind.
         */
        $wpdb->update(
            $wpdb->donationmeta,
            ['meta_value' => $storedMetaValue],
            ['donation_id' => $donation->id, 'meta_key' => DonationMetaKeys::LAST_NAME]
        );

        $html = $this->renderDonorWall(['show_avatar' => 'false']);

        $this->assertStringContainsString(esc_html($storedMetaValue), $html);
        $this->assertStringNotContainsString($storedMetaValue, $html);
    }

    /**
     * The donor wall template exposes every donationmeta key to add-ons — Tributes renders the
     * honoree out of keys core knows nothing about — so the wall has to keep handing every row
     * through to the template.
     *
     * @since TBD
     */
    public function testPassesUnmodelledDonationMetaToTheTemplate()
    {
        $donation = $this->createDonorWallDonation();

        give_update_payment_meta($donation->id, '_give_tributes_type', 'In honor of');
        give_update_payment_meta($donation->id, '_give_tributes_first_name', 'Ada');
        give_update_payment_meta($donation->id, '_give_tributes_last_name', 'Lovelace');

        $html = $this->renderDonorWall(['show_avatar' => 'false', 'show_tributes' => 'true']);

        $this->assertStringContainsString('In honor of', $html);
        $this->assertStringContainsString('Ada L.', $html);
    }

    /**
     * Guards the read path against a future reintroduction of unserialize(): no shape of stored
     * payload — top level, nested, or double-encoded — may bring a class into being.
     *
     * @since TBD
     *
     * @dataProvider objectPayloadProvider
     *
     * @param string $storedMetaValue The meta value as it sits in the donationmeta row.
     */
    public function testDoesNotInstantiateClassesFromStoredMeta(string $storedMetaValue)
    {
        global $wpdb;

        SerializedPayloadProbe::$instantiated = false;
        $donation = $this->createDonorWallDonation();

        $wpdb->update(
            $wpdb->donationmeta,
            ['meta_value' => $storedMetaValue],
            ['donation_id' => $donation->id, 'meta_key' => DonationMetaKeys::LAST_NAME]
        );

        $this->renderDonorWall(['show_avatar' => 'false']);

        $this->assertFalse(SerializedPayloadProbe::$instantiated);
    }

    /**
     * @since TBD
     *
     * @return array<string, array{0: string}>
     */
    public function objectPayloadProvider(): array
    {
        $payload = serialize(new SerializedPayloadProbe());

        return [
            'object at the top level' => [$payload],
            'object nested in an array' => [serialize(['lastName' => new SerializedPayloadProbe()])],
            'object wrapped in a serialized string' => [serialize($payload)],
        ];
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
