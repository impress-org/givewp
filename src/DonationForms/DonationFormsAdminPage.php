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
        global $submenu;

        $giveParentPageKey = 'edit.php?post_type=give_forms';

        if ( ! isset($submenu[$giveParentPageKey])) {
            return;
        }

        /** @var $giveSubmenu array [0: "Add Form", 1: "edit_give_forms", 2: "post-new.php?post_type=give_forms", ...] */
        foreach ($submenu[$giveParentPageKey] as $giveSubmenuKey => $giveSubmenu) {
            if ($this->isLegacyAddFormSubmenuLink($giveSubmenu)) {
                $submenu[$giveParentPageKey][$giveSubmenuKey][2] = 'edit.php?post_type=give_forms&page=givewp-form-builder';
            }
        }
    }

    /**
     * @unreleased
     *
     * @param $giveSubmenu array [0: "Add Form", 1: "edit_give_forms", 2: "post-new.php?post_type=give_forms", ...]
     */
    private function isLegacyAddFormSubmenuLink(array $giveSubmenu): bool
    {
        return isset($giveSubmenu[2]) && 'post-new.php?post_type=give_forms' === $giveSubmenu[2];
    }
}
