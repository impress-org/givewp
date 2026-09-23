<?php

namespace Give\Tests\Unit\DonationForms\Actions;

use Give\DonationForms\Actions\GenerateDonationFormPageUrl;
use Give\Tests\TestCase;

final class TestGenerateDonationFormPageUrl extends TestCase
{
    /**
     * @since 4.17.0
     */
    public function testShouldAddressTheFormByPostTypeAndId()
    {
        $url = (new GenerateDonationFormPageUrl())(42);

        $this->assertSame(add_query_arg(['post_type' => 'give_forms', 'p' => 42], home_url('/')), $url);
    }

    /**
     * @since 4.17.0
     */
    public function testShouldReturnTheBaseUrlWithoutAFormId()
    {
        $url = (new GenerateDonationFormPageUrl())();

        $this->assertSame(add_query_arg(['post_type' => 'give_forms'], home_url('/')), $url);
        $this->assertStringNotContainsString('p=', $url);
    }
}
