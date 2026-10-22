<?php
/**
 * @since TBD Escape output, including translated strings.
 */
?>
<style>
    .give-donation-summary-table-wrapper {
        --primary-color: <?php echo esc_attr($this->getPrimaryColor()); ?>;
    }
</style>
<div class="give-donation-summary-section">

    <?php
    if ($heading = $this->getSummaryHeading()): ?>
        <div class="heading"><?php
            echo esc_html($heading); ?></div>
    <?php
    endif; ?>

    <div class="give-donation-summary-table-wrapper">

        <table>
            <thead>
            <tr>
                <th><?php
                    esc_html_e('Donation Summary', 'give'); ?></th>
                <th>
                    <?php
                    if ($this->isMultiStep()): ?>
                        <button type="button" class="back-btn" onclick="GiveDonationSummary.handleNavigateBack(event)">
                            <?php
                            esc_html_e('Edit Donation', 'give'); ?>
                            <?php
                            include plugin_dir_path(__DIR__) . 'images/pencil.svg'; ?>
                        </button>
                    <?php
                    endif ?>
                </th>
            </tr>
            </thead>
            <tbody>


            <!-- PAYMENT AMOUNT -->
            <tr>
                <td>
                    <div><?php
                        esc_html_e('Payment Amount', 'give'); ?></div>
                </td>
                <td data-tag="amount"></td>
            </tr>


            <!-- GIVING FREQUENCY -->
            <tr>
                <td>
                    <div><?php
                        esc_html_e('Giving Frequency', 'give'); ?></div>
                    <?php
                    if ($this->isRecurringEnabled()): ?>
                        <span class="give-donation-summary-help-text js-give-donation-summary-frequency-help-text">
                            <img src="<?php
                            echo esc_url(GIVE_PLUGIN_URL . 'src/DonationSummary/resources/images/info.svg'); ?>" alt="">
                            <span>
                            <?php
                            $isMultiStep = $this->isMultiStep();
                            /* translators: 1: <button> open tag when multi-step 2: close tag when multi-step. */
                            echo sprintf(
                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the button tags are code-controlled, not user input; wp_kses_post() would strip the onclick handler needed for navigation.
                                __('Consider making this donation %srecurring%s', 'give'),
                                $isMultiStep ? '<button type="button" class="back-btn" onclick="GiveDonationSummary.handleNavigateBack(event)">' : '',
                                $isMultiStep ? '</button>' : ''
                            );
                            ?>
                            </span>
                        </span>
                    <?php
                    endif; ?>
                </td>
                <td>
                    <span data-tag="recurring"></span>
                    <span data-tag="frequency"><?php
                        esc_html_e('One time', 'give'); ?></span>
                </td>
            </tr>


            <!-- COVER DONATION FEES -->
            <?php
            if ($this->isFeeRecoveryEnabled()): ?>
                <tr class="js-give-donation-summary-fees">
                    <td>
                        <div><?php
                            echo esc_html__('Cover Donation Fees', 'give'); ?></div>
                        <span class="give-donation-summary-help-text">
                                <img src="<?php
                                echo esc_url(GIVE_PLUGIN_URL . 'src/DonationSummary/resources/images/info.svg'); ?>" alt="">
                                <?php
                                esc_html_e('Ensures 100% of your donation reaches our cause', 'give'); ?>
                            </span>
                    </td>
                    <td data-tag="fees">{fees}</td>
                </tr>
            <?php
            endif; ?>


            </tbody>
            <tfoot>


            <!-- TOTAL DONATION AMOUNT (INCLUDING FEES) -->
            <tr>
                <th><?php
                    esc_html_e('Donation Total', 'give'); ?></th>
                <th data-tag="total"></th>
            </tr>


            </tfoot>
        </table>
    </div>
</div>
