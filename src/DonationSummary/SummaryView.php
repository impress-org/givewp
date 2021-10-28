<?php

namespace Give\DonationSummary;

/**
 * @unreleased
 */
class SummaryView {

    /**
     * @unreleased
     * @var int
     */
    protected $formID;

    /**
     * @unreleased
     * @param int $formID
     */
    public function __invoke( $formID ) {
        $this->formID = $formID;

        if( 'sequoia' === $this->getFormTemplate() ) {
            if( $this->isDonationSummaryEnabled() ) {
                /**
                 * @hook give_donation_form_user_info
                 * @hook give_donation_form_before_submit
                 */
                add_action( $this->getFormTemplateLocation(), [ $this, 'render' ] );
            }
        }
    }

    /**
     * @unreleased
     */
    public function render() {
        include 'resources/views/summary.php';
    }

    /**
     * @unreleased
     * @return string
     */
    public function getFormTemplate() {
        return Give()->form_meta->get_meta( $this->formID, '_give_form_template', $single = true );
    }

    /**
     * @unreleased
     * @throws \Exception
     * @return string
     */
    public function getFormTemplateLocation() {
        $templateSettings = Give()->form_meta->get_meta( $this->formID, '_give_sequoia_form_template_settings', $single = true );
        if( isset( $templateSettings[ 'donation_summary' ] ) && isset( $templateSettings[ 'donation_summary' ][ 'location' ] ) ) {
            return $templateSettings[ 'donation_summary' ][ 'location' ];
        }
        throw new \Exception( 'Donation Summary location not set' );
    }

    /**
     * @unreleased
     * @return string
     */
    public function getSummaryHeading() {
        $templateSettings = Give()->form_meta->get_meta( $this->formID, '_give_sequoia_form_template_settings', $single = true );
        if( isset( $templateSettings[ 'donation_summary' ] ) && isset( $templateSettings[ 'donation_summary' ][ 'heading' ] ) ) {
            return $templateSettings[ 'donation_summary' ][ 'heading' ];
        }
        return '';
    }

    /**
     * @unreleased
     * @return bool
     */
    public function isDonationSummaryEnabled() {
        $templateSettings = Give()->form_meta->get_meta( $this->formID, '_give_sequoia_form_template_settings', $single = true );
        return isset( $templateSettings[ 'donation_summary' ] )
            && isset( $templateSettings[ 'donation_summary' ][ 'enabled' ] )
            && give_is_setting_enabled( $templateSettings[ 'donation_summary' ][ 'enabled' ] );
    }

    /**
     * @unreleased
     * @return bool
     */
    protected function isFeeRecoveryEnabled() {
        if( class_exists( '\GiveFeeRecovery\Helpers\Form\Form' ) ) {
            return \GiveFeeRecovery\Helpers\Form\Form::canRecoverFee( $this->formID );
        }
        return false;
    }

    /**
     * @unreleased
     * @return bool
     */
    protected function isRecurringEnabled() {
        if( class_exists( '\Give_Recurring' ) ) {
            return Give_Recurring()->is_recurring( $this->formID );
        }
        return false;
    }
}
