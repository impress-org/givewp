<?php

namespace Give\Tests\NextGen\Framework\TemplateTags\Actions;

use Give\Framework\TemplateTags\Actions\TransformTemplateTags;
use Give\Tests\TestCase;

class TestTransformTemplateTags extends TestCase {

    /**
     * @since 3.0.0
     * @return void
     */
    public function testShouldTransformTemplateTags() {
        $content = "{first_name}, your contribution means a lot and will be put to good use in making a difference. We’ve sent your donation receipt to {email}.";
        $tags = [
            '{first_name}' => 'Bill',
            '{email}' => 'bill@murray.com'
        ];

        $transformedContent = (new TransformTemplateTags())($content, $tags);

        $this->assertEquals("Bill, your contribution means a lot and will be put to good use in making a difference. We’ve sent your donation receipt to bill@murray.com.", $transformedContent);
    }
}
