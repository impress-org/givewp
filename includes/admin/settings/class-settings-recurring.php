<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @since      2.27.1 Removed React rendering element.
 *
 * @since      2.17.1
 */
class Give_Settings_Recurring_Donations_Core extends Give_Settings_Page
{
    protected $enable_save = false;

    /**
     * Give_Settings_Recurring_Donations constructor.
     *
     * @since TBD Number the placeholders and add translators comments.
     */
    public function __construct()
    {
        $this->id = 'recurring';
        $this->label = sprintf(
            /* translators: 1: Icon image markup, 2: Recommended badge markup */
            __('%1$s Recurring Donations %2$s', 'give'),
            '<img style="display: inline-block; vertical-align: middle; margin: 0 5px 2px 0; " src="' . GIVE_PLUGIN_URL . 'build/assets/dist/images/admin/black-external-icon.svg" alt="icon"/>',
            '<span class="givewp-upsells-recurring-recommended">
                <strong>' . __('RECOMMENDED', 'give') . '</strong>
            </span>'
        );


        parent::__construct();
    }


}

return new Give_Settings_Recurring_Donations_Core();
