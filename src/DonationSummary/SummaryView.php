<?php

namespace Give\DonationSummary;

class SummaryView {

    /**
     * @var int
     */
    protected $formID;

    /**
     * @param int $formID
     */
    public function __invoke( $formID ) {
        $this->formID = $formID;

        if( 'sequoia' === $this->getFormTemplate() ) {
            if( $this->isDonationSummaryEnabled() ) {
                $this->render();
            }
        }
    }

    public function render() {
        include 'resources/views/summary.php';
    }

    protected function getFormTemplate() {
        return Give()->form_meta->get_meta( $this->formID, '_give_form_template', $single = true );
    }

    protected function isDonationSummaryEnabled() {
        $templateSettings = Give()->form_meta->get_meta( $this->formID, '_give_sequoia_form_template_settings', $single = true );
        return isset( $templateSettings[ 'donation_summary' ] )
            && isset( $templateSettings[ 'donation_summary' ][ 'enabled' ] )
            && give_is_setting_enabled( $templateSettings[ 'donation_summary' ][ 'enabled' ] );
    }

    protected function isFeeRecoveryEnabled() {
        if( class_exists( '\GiveFeeRecovery\Helpers\Form\Form' ) ) {
            return \GiveFeeRecovery\Helpers\Form\Form::canRecoverFee( $this->formID );
        }
        return false;
    }

    protected function isRecurringEnabled() {
        if( class_exists( '\Give_Recurring' ) ) {
            return Give_Recurring()->is_recurring( $this->formID );
        }
        return false;
    }
}
