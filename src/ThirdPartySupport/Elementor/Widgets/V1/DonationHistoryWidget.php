<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V1;

use Elementor\Widget_Base;

/**
 * Elementor Donation History Widget.
 *
 * Elementor widget that inserts the GiveWP [donation_history] shortcode to output a donor's full donation history table.
 *
 * @since 4.7.0 migrated from givewp-elementor-widgets
 */

class DonationHistoryWidget extends Widget_Base
{
    /**
     * Get widget name.
     *
     * Retrieve Donation History widget name.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name()
    {
        return 'Donation History';
    }

    /**
     * Get widget title.
     *
     * Retrieve Donation History widget title.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title()
    {
        return __('Give Donation History (Legacy)', 'give');
    }

    /**
     * Get widget icon.
     *
     * Retrieve Donation History widget icon.
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
     * Retrieve the list of categories the Donation History widget belongs to.
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
     * Register Donation History widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access protected
     */
    protected function register_controls()
    {
        $this->start_controls_section(
            'donation_history_settings',
            [
                'label' => __('Donation History Widget', 'give'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'form_id',
            [
                'label' => __('ID', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show or hide the "ID" column.', 'give'),
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
                'description' => __('Show or hide a column with the date of the donation.', 'give'),
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
                'description' => __('Show or hide a column with the donors full name.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'no'
            ]
        );

        $this->add_control(
            'amount',
            [
                'label' => __('Amount', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show or hide a column with the amount of the donation.', 'give'),
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
                'description' => __('Show or hide a column with the status of the payment.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'no'
            ]
        );

        $this->add_control(
            'method',
            [
                'label' => __('Payment Method', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show or hide a column with the name of the payment method.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'no'
            ]
        );

        $this->add_control(
            'give_history_info',
            [
                'label' => '',
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'content_classes' => 'give-info',
                'raw' => '
					<div class="give">
						<p class="info-head">
							' . __('GIVEWP DONATION HISTORY WIDGET', 'give') . '</p>
						<p class="info-message">' . __('This is the GiveWP Donation History widget. Choose which columns you want to have appear for your donors history.', 'give') . '</p>
						<p class="give-docs-links">
							<a href="https://givewp.com/documentation/core/shortcodes/donation_history/?utm_source=plugin_settings&utm_medium=referral&utm_campaign=Free_Addons&utm_content=givelementor" rel="noopener noreferrer" target="_blank"><i class="fa fa-book" aria-hidden="true"></i>' . __('Visit the GiveWP Docs for more info on the GiveWP Donation History.', 'give') . '</a>
						</p>
				</div>'
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render the [donation_history] output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access protected
     */
    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $id = ('yes' === $settings['form_id'] ? '' : 'id="false"');
        $donor = ('yes' === $settings['donor'] ? 'donor="true"' : '');
        $date = ('yes' === $settings['date'] ? '' : 'date="false"');
        $amount = ('yes' === $settings['amount'] ? '' : 'amount="false"');
        $status = ('yes' === $settings['status'] ? 'status="true"' : '');
        $method = ('yes' === $settings['method'] ? 'payment_method="true"' : '');

        if (!\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            $html = do_shortcode(
                '
			[donation_history '
                    . $id . ' '
                    . $donor . ' '
                    . $date . ' '
                    . $amount . ' '
                    . $status . ' '
                    . $method .
                    ']'
            );
        } else {
            ob_start(); ?>
			<style>
				.give-mobile-title {display:none;}
			</style>
			<table id="give_user_history" class="give-table">
				<thead>
					<tr class="give-donation-row">
						<?php if ($settings['form_id'] === 'yes'): ?><th scope="col" class="give-donation-id>">ID</th><?php endif; ?>
						<?php if ($settings['date'] === 'yes'): ?><th scope="col" class="give-donation-date>">Date</th><?php endif; ?>
						<?php if ($settings['donor'] === 'yes'): ?><th scope="col" class="give-donation-donor>">Donor</th><?php endif; ?>
						<?php if ($settings['amount'] === 'yes'): ?><th scope="col" class="give-donation-amount>">Amount</th><?php endif; ?>
						<?php if ($settings['status'] === 'yes'): ?><th scope="col" class="give-donation-status>">Status</th><?php endif; ?>
						<?php if ($settings['method'] === 'yes'): ?><th scope="col" class="give-donation-payment_method>">Payment Method</th><?php endif; ?>
						<th scope="col" class="give-donation-details>">Details</th>
					</tr>
				</thead>
				<tbody>
					<tr class="give-donation-row">
						<?php if ($settings['form_id'] === 'yes'): ?><td class="give-donation-id"><span class="give-mobile-title">ID</span>57</td><?php endif; ?>
						<?php if ($settings['date'] === 'yes'): ?><td class="give-donation-date"><span class="give-mobile-title">Date</span>April 28, 2020</td><?php endif; ?>
						<?php if ($settings['donor'] === 'yes'): ?><td class="give-donation-donor"><span class="give-mobile-title">Donor</span>Test Donor</td><?php endif; ?>
						<?php if ($settings['amount'] === 'yes'): ?><td class="give-donation-amount">
							<span class="give-mobile-title">Amount</span> <span class="give-donation-amount">
								$500.00 </span>
						</td><?php endif; ?>

						<?php if ($settings['status'] === 'yes'): ?><td class="give-donation-status"><span class="give-mobile-title">Status</span>Complete</td><?php endif; ?>
						<?php if ($settings['method'] === 'yes'): ?><td class="give-donation-payment-method"><span class="give-mobile-title">Payment Method</span>Test Donation</td><?php endif; ?>
						<td class="give-donation-details">
							<span class="give-mobile-title">Details</span><a href="#">View Receipt »</a> </td>
					</tr>
					<tr class="give-donation-row">
						<?php if ($settings['form_id'] === 'yes'): ?><td class="give-donation-id"><span class="give-mobile-title">ID</span>41</td><?php endif; ?>
						<?php if ($settings['date'] === 'yes'): ?><td class="give-donation-date"><span class="give-mobile-title">Date</span>January 2, 2020</td><?php endif; ?>
						<?php if ($settings['donor'] === 'yes'): ?><td class="give-donation-donor"><span class="give-mobile-title">Donor</span>Test Donor</td><?php endif; ?>
						<?php if ($settings['amount'] === 'yes'): ?><td class="give-donation-amount">
							<span class="give-mobile-title">Amount</span> <span class="give-donation-amount">
								$25.00 </span>
						</td><?php endif; ?>

						<?php if ($settings['status'] === 'yes'): ?><td class="give-donation-status"><span class="give-mobile-title">Status</span>Complete</td><?php endif; ?>
						<?php if ($settings['method'] === 'yes'): ?><td class="give-donation-payment-method"><span class="give-mobile-title">Payment Method</span>Test Donation</td><?php endif; ?>
						<td class="give-donation-details">
							<span class="give-mobile-title">Details</span><a href="#">View Receipt »</a> </td>
					</tr>
					<tr class="give-donation-row">
						<?php if ($settings['form_id'] === 'yes'): ?><td class="give-donation-id"><span class="give-mobile-title">ID</span>38</td><?php endif; ?>
						<?php if ($settings['date'] === 'yes'): ?><td class="give-donation-date"><span class="give-mobile-title">Date</span>December 15, 2019</td><?php endif; ?>
						<?php if ($settings['donor'] === 'yes'): ?><td class="give-donation-donor"><span class="give-mobile-title">Donor</span>Test Donor</td><?php endif; ?>
						<?php if ($settings['amount'] === 'yes'): ?><td class="give-donation-amount">
							<span class="give-mobile-title">Amount</span> <span class="give-donation-amount">
								$50.00 </span>
						</td><?php endif; ?>

						<?php if ($settings['status'] === 'yes'): ?><td class="give-donation-status"><span class="give-mobile-title">Status</span>Complete</td><?php endif; ?>
						<?php if ($settings['method'] === 'yes'): ?><td class="give-donation-payment-method"><span class="give-mobile-title">Payment Method</span>Test Donation</td><?php endif; ?>
						<td class="give-donation-details">
							<span class="give-mobile-title">Details</span><a href="#">View Receipt »</a> </td>
					</tr>
					<tr class="give-donation-row">
						<?php if ($settings['form_id'] === 'yes'): ?><td class="give-donation-id"><span class="give-mobile-title">ID</span>25</td><?php endif; ?>
						<?php if ($settings['date'] === 'yes'): ?><td class="give-donation-date"><span class="give-mobile-title">Date</span>October 23, 2019</td><?php endif; ?>
						<?php if ($settings['donor'] === 'yes'): ?><td class="give-donation-donor"><span class="give-mobile-title">Donor</span>Test Donor</td><?php endif; ?>
						<?php if ($settings['amount'] === 'yes'): ?><td class="give-donation-amount">
							<span class="give-mobile-title">Amount</span> <span class="give-donation-amount">
								$28.00 </span>
						</td><?php endif; ?>

						<?php if ($settings['status'] === 'yes'): ?><td class="give-donation-status"><span class="give-mobile-title">Status</span>Complete</td><?php endif; ?>
						<?php if ($settings['method'] === 'yes'): ?><td class="give-donation-payment-method"><span class="give-mobile-title">Payment Method</span>Test Donation</td><?php endif; ?>
						<td class="give-donation-details">
							<span class="give-mobile-title">Details</span><a href="#">View Receipt »</a> </td>
					</tr>

				</tbody>
			</table>

<?php
            ob_get_contents();
            $html = ob_get_clean();
        }

        echo '<div class="givewp-elementor-widget donation-history">';

        echo $html;

        echo '</div>';
    }
}
