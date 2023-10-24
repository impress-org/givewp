<?php

namespace Give\Tests\Unit\DonationForms\Properties;

use Give\DonationForms\Properties\FormSettings;
use Give\Tests\TestCase;

final class FormSettingsTest extends TestCase
{
    public function testAddSlashesRecursive()
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
            (new FormSettings)->addSlashesRecursive($array)
        );
    }
}
