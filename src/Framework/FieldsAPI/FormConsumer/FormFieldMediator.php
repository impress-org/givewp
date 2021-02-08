<?php

namespace Give\Framework\FieldsAPI\FormConsumer;

use Give\Framework\FieldsAPI\FieldCollection;

class FormFieldMediator {

    /**
     * A "full-ish" list of available actions.
     * @note The `give_` prefix has been removed for interoperability.
     * @note Update to account for previously deprecated hooks.
     * @link https://givewp.com/documentation/developers/how-to-create-custom-form-fields/
     * @link https://givewp.com/add-content-donation-forms/
     */
    const TEMPLATE_HOOKS = [
        'before_donation_levels',
        'after_donation_amount',
        'after_donation_levels',
        'payment_mode_select',
        'payment_mode_top',
        'payment_mode_before_gateways',
        'payment_mode_after_gateways',
        'payment_mode_after_gateways_wrap',
        'payment_mode_bottom',
        'donation_form',
        'purchase_form_top',
        'donation_form_register_login_fields',
        'donation_form_before_cc_form',
        'cc_form',
        'before_cc_fields',
        'before_cc_expiration',
        'after_cc_expiration',
        'after_cc_fields',
        'donation_form_after_cc_form',
        'purchase_form_bottom',
    ];

    public function __invoke() {
        foreach( self::TEMPLATE_HOOKS as $hook ) {
            $this->setupTemplateHook( $hook );
        }
    }

    /**
     * @param string $hook A template hook for custom field output.
     */
    public function setupTemplateHook( $hook ) {
        $fieldCollection = new FieldCollection( 'root' );
        do_action( "give_fields_$hook", $fieldCollection );
        add_action( "give_$hook", function( $formID ) use ( $fieldCollection ) {
            foreach( $fieldCollection->getFields() as $field ) {
                FieldView::render( $field );
            }
        });
    }
}
