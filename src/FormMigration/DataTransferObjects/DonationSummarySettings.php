<?php

namespace Give\FormMigration\DataTransferObjects;

/**
 * The `Sequoia` and `Classic` templates share a structure for Donation Summary settings.
 *
 * @since 3.0.0
 */
class DonationSummarySettings
{
    /** @var bool */
    protected $enabled;

    /** @var string */
    protected $heading;

    /** @var string */
    protected $location;

    /**
     * @param string $enabled `donation_summary_enabled` A string value of `enabled` or `disabled`.
     * @param string $heading `donation_summary_heading` ie 'Here\'s what you\'re about to donate:'
     * @param string $location `donation_summary_location` A string representing a corresponding template hook.
     */
    public function __construct($enabled, $heading, $location)
    {
        $this->enabled = give_is_setting_enabled($enabled);
        $this->heading = $heading;
        $this->location = $location;
    }

    public static function make($settings)
    {
        return new self(
            $settings['donation_summary_enabled'],
            $settings['donation_summary_enabled'],
            $settings['donation_summary_location']
        );
    }

    public function getHeading(): string
    {
        return $this->heading ?: __('Donation Summary', 'give');
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @since 3.0.0
     *
     * @note `give_donation_form_user_info` is presented as "Before payment fields".
     * @note `give_donation_form_before_submit` is the default location, presented as "After payment fields".
     */
    public function isBeforePaymentFields(): bool
    {
        return 'give_donation_form_user_info' === $this->location;
    }
}
