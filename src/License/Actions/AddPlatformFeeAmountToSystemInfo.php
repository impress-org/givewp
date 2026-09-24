<?php

namespace Give\License\Actions;

use Give\License\Repositories\LicenseRepository;

/**
 * @since 4.3.0
 */
class AddPlatformFeeAmountToSystemInfo
{
    /**
     * Callback for `give_add_system_info_configuration` that adds the platform fee amount to the system info table.
     * @since TBD Escape output.
     * @since 4.3.0
     */
    public function __invoke($settings)
    {
        /**
         * @var LicenseRepository $licenseRepository
         */
        $licenseRepository = give(LicenseRepository::class);
        $label = __('Platform Fee Amount', 'give');

        ?>
        <tr>
            <td data-export-label="<?php echo esc_attr($label); ?>">
                <?php echo esc_html($label); ?>:
            </td>
            <td class="help">
                <?php echo wp_kses_post(Give()->tooltips->render_help(__('The GiveWP Platform Fee Percentage.', 'give'))); ?>
            </td>
            <td>
                <?php echo esc_html($licenseRepository->getPlatformFeePercentage() . '%'); ?>
            </td>
        </tr>
        <?php
    }
}
