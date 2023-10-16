<?php

namespace Give\Tests\NextGen\Framework\TemplateTags;

use Give\Donations\Models\Donation;
use Give\Framework\TemplateTags\DonationTemplateTags;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class TestDonationTemplateTags extends TestCase {
    use RefreshDatabase;

    /**
     * @since 3.0.0
     */
    public function testShouldTransformDonationTemplateTags() {

        $donation = Donation::factory()->create([
            'firstName' => 'Bill',
            'email' => 'bill@murray.com'
        ]);

        $content = "{first_name}, your contribution means a lot and will be put to good use in making a difference. We’ve sent your donation receipt to {email}.";

        $tags = new DonationTemplateTags($donation, $content);

        $this->assertEquals("Bill, your contribution means a lot and will be put to good use in making a difference. We’ve sent your donation receipt to bill@murray.com.", $tags->getContent());
    }
}
