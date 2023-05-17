<?php

/**
 * @unreleased Removed React rendering element.
 *
 * @since      2.17.1
 */
class Give_Settings_Recurring_Donations_Core extends Give_Settings_Page
{
    protected $enable_save = false;

    /**
     * Give_Settings_Recurring_Donations constructor.
     */
    public function __construct()
    {
        $this->id = 'recurring';
        $this->label = sprintf(
            __('%s Recurring Donations %s', 'give'),
            '<img style="display: inline-block; vertical-align: middle; margin: 0 5px 2px 0; " src="' . GIVE_PLUGIN_URL . '/assets/dist/images/admin/black-external-icon.svg" alt="icon"/>',
            '<span style="display: inline-block; vertical-align: middle; margin: 0 0 2px 8px; padding: 4px 8px; background:#F2CC0C; font-size: 10px; border-radius: 4px;">
                <strong>' . __('RECOMMENDED', 'give') . '</strong>
            </span>'
        );


        parent::__construct();
    }


}

return new Give_Settings_Recurring_Donations_Core();
