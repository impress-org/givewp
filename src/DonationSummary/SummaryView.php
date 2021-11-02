<?php

namespace Give\DonationSummary;

use Give\Helpers\Form\Template;

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
     * @var array
     * @unreleased
     */
    protected $templateOptions;

    /**
     * @unreleased
     * @param int $formID
     */
    public function __invoke( $formID ) {
        $this->formID = $formID;
        $this->templateOptions = Template::getOptions( $formID );

        if( 'sequoia' === Template::getActiveID( $formID ) ) {
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
     * @throws \Exception
     * @return string
     */
    public function getFormTemplateLocation() {
        if( isset( $this->templateOptions[ 'donation_summary' ] ) && isset( $this->templateOptions[ 'donation_summary' ][ 'location' ] ) ) {
            return $this->templateOptions[ 'donation_summary' ][ 'location' ];
        }
        return 'give_donation_form_before_submit'; // Default location.
    }

    /**
     * @unreleased
     * @return string
     */
    public function getSummaryHeading() {
        if( isset( $this->templateOptions[ 'donation_summary' ] ) && isset( $this->templateOptions[ 'donation_summary' ][ 'heading' ] ) ) {
            return $this->templateOptions[ 'donation_summary' ][ 'heading' ];
        }
        return '';
    }

    /**
     * @unreleased
     * @return bool
     */
    public function isDonationSummaryEnabled() {
        return isset( $this->templateOptions[ 'donation_summary' ] )
            && isset( $this->templateOptions[ 'donation_summary' ][ 'enabled' ] )
            && give_is_setting_enabled( $this->templateOptions[ 'donation_summary' ][ 'enabled' ] );
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
