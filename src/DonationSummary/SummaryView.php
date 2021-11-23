<?php

namespace Give\DonationSummary;

use Give\Helpers\Form\Template;

/**
 * @since 2.17.0
 */
class SummaryView
{

    /**
     * @since 2.17.0
     * @var int
     */
    protected $formID;

    /**
     * @since 2.17.0
     * @var array
     */
    protected $templateOptions;

    /**
     * @since 2.17.0
     *
     * @param int $formID
     */
    public function __invoke($formID)
    {
        $this->formID = $formID;
        $this->templateOptions = Template::getOptions($formID);

        /**
         * @hook give_donation_form_user_info
         * @hook give_donation_form_before_submit
         */
        add_action($this->getFormTemplateLocation(), [$this, 'maybeRender']);
    }

    public function maybeRender()
    {
        if ($this->isDonationSummaryEnabled()) {
            if ('sequoia' === Template::getActiveID($this->formID)) {
                $this->render();
            }
        }
    }

    /**
     * @since 2.17.0
     */
    public function render()
    {
        include 'resources/views/summary.php';
    }

    public function getPrimaryColor()
    {
        return $this->templateOptions['visual_appearance']['primary_color'];
    }

    /**
     * @since 2.17.0
     * @return string
     * @throws \Exception
     */
    public function getFormTemplateLocation()
    {
        if (isset($this->templateOptions['payment_information']) && isset($this->templateOptions['payment_information']['donation_summary_location'])) {
            return $this->templateOptions['payment_information']['donation_summary_location'];
        }

        return 'give_donation_form_before_submit'; // Default location.
    }

    /**
     * @since 2.17.0
     * @return string
     */
    public function getSummaryHeading()
    {
        if (isset($this->templateOptions['payment_information']) && isset($this->templateOptions['payment_information']['donation_summary_heading'])) {
            return $this->templateOptions['payment_information']['donation_summary_heading'];
        }

        return '';
    }

    /**
     * @since 2.17.0
     * @return bool
     */
    public function isDonationSummaryEnabled()
    {
        return isset($this->templateOptions['payment_information'])
               && isset($this->templateOptions['payment_information']['donation_summary_enabled'])
               && give_is_setting_enabled($this->templateOptions['payment_information']['donation_summary_enabled']);
    }

    /**
     * @since 2.17.0
     * @return bool
     */
    protected function isFeeRecoveryEnabled()
    {
        if (class_exists('\GiveFeeRecovery\Helpers\Form\Form')) {
            return \GiveFeeRecovery\Helpers\Form\Form::canRecoverFee($this->formID);
        }

        return false;
    }

    /**
     * @since 2.17.0
     * @return bool
     */
    protected function isRecurringEnabled()
    {
        if (class_exists('\Give_Recurring')) {
            return Give_Recurring()->is_recurring($this->formID);
        }

        return false;
    }
}
