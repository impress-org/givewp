<?php

namespace Give\Tests\Unit\Email;

use Give\Email\GlobalSettingValidator;

final class GlobalSettingValidatorTest extends \Give\Tests\TestCase
{
    /** @test */
    public function it_does_not_validate_empty_values()
    {
        $hook = "give_admin_settings_sanitize_option_donation-receipt_recipient";
        add_filter($hook, [new GlobalSettingValidator, 'validateSetting']);
        $value = apply_filters($hook, null);

        $this->assertEquals(null, $value);
    }
}
