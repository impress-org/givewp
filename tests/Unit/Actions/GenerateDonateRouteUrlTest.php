<?php

namespace Give\Tests\Unit\Actions;

use Give\DonationForms\Actions\GenerateDonateRouteUrl;
use Give\DonationForms\Routes\DonateRouteSignature;
use Give\Tests\TestCase;

class GenerateDonateRouteUrlTest extends TestCase
{
    /**
     * @since TBD Build the expected URL from the URL's own expiration instead of reading the clock again.
     * @since 4.3.0 Use trailingslashit() method to prevent errors on websites installed in subdirectories
     * @since 3.0.0
     *
     * @return void
     */
    public function testShouldReturnValidUrl()
    {
        $url = (new GenerateDonateRouteUrl())();

        // The URL is signed with an expiration one day from the current second. Build the expected URL
        // from that expiration, since a second ticking over before reading the clock again would change it.
        parse_str((string)wp_parse_url($url, PHP_URL_QUERY), $query);
        $expiration = $query['givewp-route-signature-expiration'];

        $this->assertEqualsWithDelta(current_datetime()->modify('+1 day')->getTimestamp(), (int)$expiration, 5);

        $signature = new DonateRouteSignature('givewp-donate', $expiration);

        $queryArgs = [
            'givewp-route' => 'donate',
            'givewp-route-signature' => $signature->toHash(),
            'givewp-route-signature-id' => 'givewp-donate',
            'givewp-route-signature-expiration' => $signature->expiration,
        ];

        $mockUrl = esc_url_raw(add_query_arg($queryArgs, trailingslashit(home_url())));

        $this->assertSame($mockUrl, $url);
    }
}
