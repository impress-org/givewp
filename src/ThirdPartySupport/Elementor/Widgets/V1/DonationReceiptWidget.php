<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V1;

use Elementor\Widget_Base;

/**
 * Elementor Donation Receipt Widget.
 *
 * Elementor widget that inserts the GiveWP [give_receipt] shortcode to output a donor's full Donation Receipt table.
 *
 * @since 4.7.0 migrated from givewp-elementor-widgets
 */

class DonationReceiptWidget extends Widget_Base
{
    /**
     * Get widget name.
     *
     * Retrieve Donation Receipt widget name.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name()
    {
        return 'Donation Receipt';
    }

    /**
     * Get widget title.
     *
     * Retrieve Donation Receipt widget title.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title()
    {
        return __('Give Donation Receipt (Legacy)', 'give');
    }

    /**
     * Get widget icon.
     *
     * Retrieve Donation Receipt widget icon.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon()
    {
        return 'give-icon';
    }

    /**
     * Get widget categories.
     *
     * Retrieve the list of categories the Donation Receipt widget belongs to.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access public
     *
     * @return array Widget categories.
     */
    public function get_categories()
    {
        return ['givewp-category-legacy'];
    }

    /**
     * Widget inner wrapper.
     *
     * Use optimized DOM structure, without the inner wrapper.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access public
     */
    public function has_widget_inner_wrapper(): bool
    {
        return false;
    }

    /**
     * Register Donation Receipt widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access protected
     */
    protected function register_controls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('GiveWP Donation Receipt Widget', 'give'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'error',
            [
                'label' => __('Error Message', 'give'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'description' => __('Custom text to show if there is an error showing the receipt table.', 'give'),
                'default' => __('You are missing the donation id to view this donation receipt.', 'give'),
                'placeholder' => __('You are missing the donation id to view this donation receipt.', 'give'),
            ]
        );

        $this->add_control(
            'success',
            [
                'label' => __('Success Message', 'give'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'description' => __('Custom text to show if the donation was successful.', 'give'),
                'default' => __('Thank you for your donation.', 'give'),
                'placeholder' => __('Thank you for your donation.', 'give'),
            ]
        );

        $this->add_control(
            'price',
            [
                'label' => __('Donation Total', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show or hide a row with the total donation amount.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'yes'
            ]
        );

        $this->add_control(
            'donor',
            [
                'label' => __('Donor', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show or hide a row with the donors full name.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'yes'
            ]
        );

        $this->add_control(
            'date',
            [
                'label' => __('Date', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show or hide a row with the date of the donation.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'yes'
            ]
        );

        $this->add_control(
            'method',
            [
                'label' => __('Payment Method', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show or hide a row with the name of the payment type (credit card, offline, etc).', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'yes'
            ]
        );

        $this->add_control(
            'payment_id',
            [
                'label' => __('Payment ID', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show or hide a row with the ID of the donation.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'yes'
            ]
        );

        $this->add_control(
            'status',
            [
                'label' => __('Status', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show or hide a row with the status of the payment.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'no'
            ]
        );

        $this->add_control(
            'company',
            [
                'label' => __('Company Name', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show or hide a row with the company name the donor provided.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'no'
            ]
        );

        $this->add_control(
            'status_notice',
            [
                'label' => __('Payment Status Notice', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show or hide an alert above the receipt table showing the status of the donation payment.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'yes'
            ]
        );

        $this->add_control(
            'give_receipt_info',
            [
                'label' => '',
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'content_classes' => 'give-info',
                'raw' => '
					<div class="give">
						<p class="info-head">
							' . __('GIVEWP RECEIPT WIDGET', 'give') . '</p>
						<p class="info-message">' . __('This is the GiveWP Receipt widget. Choose the columns you want to have appear for your donor receipts.', 'give') . '</p>
						<p class="info-message"><strong>' . __('NOTE:', 'give') . '</strong> ' . __('This is a sample receipt with all fields exposed. The alerts and info will show correctly for your donors. This receipt is just for preview/editing purposes.', 'give') . '</p>
						<p class="give-docs-links">
							<a href="https://givewp.com/documentation/core/shortcodes/give_receipt/?utm_source=plugin_settings&utm_medium=referral&utm_campaign=Free_Addons&utm_content=givelementor" rel="noopener noreferrer" target="_blank"><i class="fa fa-book" aria-hidden="true"></i>' . __('Visit the GiveWP Docs for more info on the GiveWP Donation Receipt.', 'give') . '</a>
						</p>
				</div>'
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render the [give_receipt] output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access protected
     */
    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $error = esc_html($settings['error']);
        $success = esc_html($settings['success']);
        $price = ('yes' === $settings['price'] ? '' : 'price="false"');
        $donor = ('yes' === $settings['donor'] ? '' : 'donor="false"');
        $date = ('yes' === $settings['date'] ? '' : 'date="false"');
        $method = ('yes' === $settings['method'] ? '' : 'payment_method="false"');
        $id = ('yes' === $settings['payment_id'] ? '' : 'payment_id="false"');
        $status = ('yes' === $settings['status'] ? 'payment_status="true"' : '');
        $company = ('yes' === $settings['company'] ? 'company_name="true"' : '');
        $notice = ('yes' === $settings['status_notice'] ? '' : 'status_notice="false"');

        // Add-on compatibility
        // Adding PDF Receipt row, and Subscription table
        $pdfreceipts = (class_exists('Give_PDF_Receipts')) ? "true" : "false";
        $recurring = (class_exists('Give_Recurring')) ? "true" : "false";

        if (!\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            $html = do_shortcode('
			[give_receipt '
                . $error . ' '
                . $price . ' '
                . $donor . ' '
                . $date . ' '
                . $method . ' '
                . $id . ' '
                . $status . ' '
                . $company . ' '
                . $notice .
                ']'
            );

            echo '<div class="givewp-elementor-widget give_receipt">';

            echo $html;

            echo '</div>';
        } else {
            ?>
			<div id="give-receipt">
				<div class="give_notices give_errors" id="give_error_fail">
					<p class="give_notice give_error">
						<?php echo (!empty($error) ? $error : __('You are missing the donation ID to view this donation receipt.', 'give')); ?>
					</p>
				</div>
				<?php if ('yes' == $settings['status_notice']) : ?>
				<div class="give_notices give_errors" id="give_error_success">
					<p class="give_notice give_success">
						<?php echo (!empty($success) ? $success : __('Thank you for your donation.', 'give')); ?>
					</p>
				</div>
				<?php endif; ?>
				<table id="give_donation_receipt" class="give-table">
					<thead>
						<tr>
							<th scope="colgroup" colspan="2">
								<span class="give-receipt-thead-text"><?php _e('Donation Receipt', 'give'); ?></span>
							</th>
						</tr>
					</thead>

					<tbody>
						<?php
                        if ('yes' == $settings['donor']) : ?>
						<tr>
							<td scope="row"><strong><?php _e('Donor', 'give'); ?></strong></td>
							<td><?php _e('Test Donor', 'give'); ?></td>
						</tr>
						<?php endif;
                        if ('yes' == $settings['company']) : ?>
						<tr>
							<td scope="row"><strong><?php _e('Company Name', 'give') ;?></strong></td>
							<td>Impress.org</td>
						</tr>
						<?php endif;
                        if ('yes' == $settings['date']) : ?>
						<tr>
							<td scope="row"><strong><?php _e('Date', 'give'); ?></strong></td>
							<td><?php _e('April 18, 2020' , 'give') ;?></td>
						</tr>
						<?php endif;
                        if ('yes' == $settings['price']) : ?>
						<tr>
							<td scope="row"><strong><?php _e('Total Donation' ,'give'); ?></strong></td>
							<td>$25.00</td>
						</tr>
						<?php endif; ?>
						<tr>
							<td scope="row"><strong><?php _e('Donation' , 'give'); ?></strong></td>
							<td><?php _e('First Form', 'give'); ?><span class="donation-level-text-wrap"></span></td>
						</tr>
						<?php
                        if ('yes' == $settings['status']) : ?>
						<tr>
							<td scope="row"><strong><?php _e('Donation Status', 'give'); ?></strong></td>
							<td><?php _e('Complete', 'give'); ?></td>
						</tr>
						<?php endif;
                        if ('yes' == $settings['payment_id']) : ?>
						<tr>
							<td scope="row"><strong><?php _e('Donation ID', 'give');?></strong></td>
							<td>3</td>
						</tr>
						<?php endif;
                        if ('yes' == $settings['method']) : ?>
						<tr>
							<td scope="row"><strong><?php _e('Payment Method' , 'give'); ?></strong></td>
							<td><?php _e('Test Donation', 'give'); ?></td>
						</tr>
						<?php endif;
                        if ('true' == $pdfreceipts) : ?>
						<tr>
							<td><strong><?php _e('Receipt', 'give'); ?>:</strong></td>
							<td><a class="give_receipt_link" title="Download Receipt" href="#"><?php _e('Download Receipt', 'give');?> »</a></td>
						</tr>
						<?php endif;?>
					</tbody>
				</table>

				<?php if ('true' == $recurring) : ?>
				<table id="give-subscription-receipt" class="give-table">

					<thead>
						<tr>
							<th scope="colgroup" colspan="2">
								<span class="give-receipt-thead-text"><?php _e('Subscription Details', 'give'); ?></span>
							</th>
						</tr>
					</thead>

					<tbody>

						<tr>
							<td scope="row"><strong><?php _e('Subscription:', 'give'); ?></strong></td>
							<td>
								<span class="give-subscription-billing-cycle">$25.00 / <?php _e('Monthly', 'give'); ?></span>
							</td>
						</tr>
						<tr>
							<td scope="row"><strong><?php _e('Status:', 'give'); ?></strong></td>
							<td>
								<span class="give-subscription-status"><span class="give-donation-status status-active"><span class="give-donation-status-icon"></span> <?php _e('Active', 'give'); ?></span></span>
							</td>
						</tr>
						<tr>
							<td scope="row"><strong><?php _e('Renewal Date:', 'give'); ?></strong></td>
							<td><span class="give-subscription-renewal-date"><?php _e('June 4, 2020', 'give'); ?></span></td>
						</tr>
						<tr>
							<td scope="row"><strong><?php _e('Progress:', 'give'); ?></strong></td>
							<td><span class="give-subscription-times-billed">1 / <?php _e('Ongoing', 'give'); ?></span>
							</td>
						</tr>

					</tbody>
				</table>
				<a href="#" class="give-recurring-manage-subscriptions-receipt-link"><?php _e('Manage Subscriptions', 'give'); ?> »</a>
			</div>
		<?php
                 endif; // End if Recurring Donations is active.
        }
    }
}
