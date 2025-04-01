<?php

namespace Give\DonationForms;

use Give\Helpers\Language;

/**
 * @since 3.22.0 Add locale support
 * @since 3.15.0
 */
class DonationFormsAdminPage
{
    /**
     * @since 4.0.0 Remove logic to add the "Add Form" menu item
     * @since 3.15.0
     */
    public function addFormSubmenuLink()
    {
        remove_submenu_page('edit.php?post_type=give_forms', 'post-new.php?post_type=give_forms');
    }
}
