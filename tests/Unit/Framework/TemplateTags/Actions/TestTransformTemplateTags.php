<?php

namespace Give\Tests\NextGen\Framework\TemplateTags\Actions;

use Give\NextGen\Framework\TemplateTags\Actions\TransformTemplateTags;
use Give\Tests\TestCase;

class TestTransformTemplateTags extends TestCase {

    /**
     * @since 0.1.0
     * @return void
     */
    public function testShouldTransformTemplateTags() {
        $content = "{donation.firstName}, your contribution means a lot and will be put to good use in making a difference. We’ve sent your donation receipt to {donation.email}.";
        $tags = [
            '{donation.firstName}' => 'Bill',
            '{donation.email}' => 'bill@murray.com'
        ];

        $transformedContent = (new TransformTemplateTags())($content, $tags);

        $this->assertEquals("Bill, your contribution means a lot and will be put to good use in making a difference. We’ve sent your donation receipt to bill@murray.com.", $transformedContent);
    }
}