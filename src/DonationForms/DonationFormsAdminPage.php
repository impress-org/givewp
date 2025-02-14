<?php

namespace Give\DonationForms;

/**
 * @since 3.15.0
 */
class DonationFormsAdminPage
{
    /**
     * @unreleased Remove logic to add the "Add Form" menu item
     * @since 3.15.0
     */
    public function addFormSubmenuLink()
    {
        remove_submenu_page('edit.php?post_type=give_forms', 'post-new.php?post_type=give_forms');
    }
}
