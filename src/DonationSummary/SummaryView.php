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
        $this->render();
    }

    public function render() {
        include 'resources/views/summary.php';
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
