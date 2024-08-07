<?php

namespace Give\DonationForms;

/**
 * @unreleased
 */
class DonationFormsAdminPage
{
    /**
     * @unreleased
     */
    public function addFormSubmenuLink()
    {
        remove_submenu_page('edit.php?post_type=give_forms', 'post-new.php?post_type=give_forms');
        add_submenu_page('edit.php?post_type=give_forms', __('Add Form', 'give'), __('Add Form', 'give'),
            'edit_give_forms',
            'edit.php?post_type=give_forms&page=givewp-form-builder', '', 1);
    }
}
