<?php

/**
 * @since 2.17.1
 */
class Give_Settings_Recurring_Donations_Core extends Give_Settings_Page
{
    const CONTAINER_ID = 'give-in-plugin-upsells';

    protected $enable_save = false;

    /**
     * Give_Settings_Recurring_Donations constructor.
     */
    public function __construct()
    {
        $this->id    = 'recurring';
        $this->label = __('Recurring Donations', 'give-recurring');

        parent::__construct();

        add_action('give_admin_field_' . self::CONTAINER_ID, [$this, 'render']);
    }

    /**
     * @inheritDoc
     */
    public function get_settings()
    {
        return [
            [
                'id'   => self::CONTAINER_ID,
                'type' => self::CONTAINER_ID,
            ],
        ];
    }

    /**
     * Render settings page
     */
    public function render()
    {
        echo '<svg style="display: none"><path id="give-in-plugin-upsells-checkmark" d="M5.595 11.373.72 6.498a.75.75 0 0 1 0-1.06l1.06-1.061a.75.75 0 0 1 1.061 0L6.125 7.66 13.159.627a.75.75 0 0 1 1.06 0l1.061 1.06a.75.75 0 0 1 0 1.061l-8.625 8.625a.75.75 0 0 1-1.06 0Z" fill="currentColor"/></svg>';
        printf('<div id="%s"></div>', self::CONTAINER_ID);
    }
}

return new Give_Settings_Recurring_Donations_Core();
