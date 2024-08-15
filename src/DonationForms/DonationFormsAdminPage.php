<?php

namespace Give\DonationForms;

/**
 * @since 3.15.0
 */
class DonationFormsAdminPage
{
    /**
     * @since 3.15.0
     */
    public function addFormSubmenuLink()
    {
        remove_submenu_page('edit.php?post_type=give_forms', 'post-new.php?post_type=give_forms');
        add_submenu_page('edit.php?post_type=give_forms', __('Add Form', 'give'), __('Add Form', 'give'),
            'edit_give_forms',
            'edit.php?post_type=give_forms&page=givewp-form-builder', '', 1);
    }
}
