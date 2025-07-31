<?php

namespace Give\ThirdPartySupport\Elementor\Controls\ElementorDonationFormControl;

class ElementorDonationFormControl extends \Elementor\Control_Select {

    public function get_type() {
        return 'givewp_donation_form_control';
    }

    public function enqueue()
	{
        wp_enqueue_script( 'givewp-donation-form-control', GIVE_PLUGIN_URL . 'src/ThirdPartySupport/Elementor/Controls/ElementorDonationFormControl/control.js', ['jquery'], '1.0.0', true );
	}
}
