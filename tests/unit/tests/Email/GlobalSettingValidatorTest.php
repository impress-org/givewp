<?php

use Give\Email\GlobalSettingValidator;

final class GlobalSettingValidatorTest extends Give_Unit_Test_Case
{
    /** @test */
    public function it_does_not_validate_empty_values()
    {
        $hook = "give_admin_settings_sanitize_option_donation-receipt_recipient";
        add_filter($hook, [ new GlobalSettingValidator, 'validateSetting']);
        $value = apply_filters( $hook, null );

        $this->assertEquals( null, $value );
    }
}
