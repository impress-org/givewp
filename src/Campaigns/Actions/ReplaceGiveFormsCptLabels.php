<?php

namespace Give\Campaigns\Actions;

/**
 * @since 4.0.0
 */
class ReplaceGiveFormsCptLabels
{
    /**
     * @since 4.0.0
     */
    public function __invoke(array $labels): array
    {
        $labels['add_new_item'] = __('Add New Campaign Form', 'give');
        $labels['edit_item'] = __('Edit Campaign Form', 'give');
        $labels['name_admin_bar'] = __('Campaign', 'give');

        return $labels;
    }
}
