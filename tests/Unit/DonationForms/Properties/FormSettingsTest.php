<?php

namespace Give\Tests\Unit\DonationForms\Properties;

use Give\Tests\TestCase;

final class FormSettingsTest extends TestCase
{
    public function testEscapeDoubleQuotes()
    {
        $array = [
            'one' => [
                'aye' => 'This is "a" test',
                'bee' => [
                    'see' => 'This is "another" test',
                ]
            ]
        ];

        $this->assertEquals(
            [
                'one' => [
                    'aye' => 'This is \"a\" test',
                    'bee' => [
                        'see' => 'This is \"another\" test',
                    ]
                ]
            ],
            wp_slash($array)
        );
    }
}
