<?php

namespace Give\Addon;

class Links
{
    /**
     * Add settings link
     * @return array
     * @since 0.1.0
     */
    public function __invoke($actions)
    {
        $newActions = array(
            'settings' => sprintf(
                '<a href="%1$s">%2$s</a>',
                admin_url('edit.php?post_type=give_forms&page=give-settings&tab=give'),
                __('Settings', 'give')
            ),
        );

        return array_merge($newActions, $actions);
    }
}
