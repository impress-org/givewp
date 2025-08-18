<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V1;

use Elementor\Widget_Base;

/**
 * Elementor GiveWP Subscriptions Widget.
 *
 * Elementor widget that inserts the GiveWP [give_subscriptions] shortcode to output a donor's full donation history table.
 *
 * @since 4.7.0 migrated from givewp-elementor-widgets
 */

class GiveSubscriptionsWidget extends Widget_Base
{
    /**
     * Get widget name.
     *
     * Retrieve GiveWP Subscriptions widget name.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name()
    {
        return 'GiveWP Subscriptions';
    }

    /**
     * Get widget title.
     *
     * Retrieve GiveWP Subscriptions widget title.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title()
    {
        return __('Give Subscriptions (Legacy)', 'give');
    }

    /**
     * Get widget icon.
     *
     * Retrieve GiveWP Subscriptions widget icon.
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
     * Retrieve the list of categories the GiveWP Subscriptions widget belongs to.
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
     * Register GiveWP Subscriptions widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access protected
     */
    protected function register_controls()
    {
        $this->start_controls_section(
            'give_subscriptions_settings',
            [
                'label' => __('GiveWP Subscriptions Widget', 'give'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_status',
            [
                'label' => __('Status', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show or hide the subscription status column.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'yes'
            ]
        );

        $this->add_control(
            'show_renewal_date',
            [
                'label' => __('Renewal Date', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show or hide a column with the subscription renewal date.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'yes'
            ]
        );

        $this->add_control(
            'show_progress',
            [
                'label' => __('Progress', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show or hide a column with progress of the subscription.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'no'
            ]
        );

        $this->add_control(
            'show_start_date',
            [
                'label' => __('Start Date', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show or hide a column with the subscription start date.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'no'
            ]
        );

        $this->add_control(
            'show_end_date',
            [
                'label' => __('End Date', 'give'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Show or hide a column with the subscription end date.', 'give'),
                'label_on' => __('Show', 'give'),
                'label_off' => __('Hide', 'give'),
                'default' => 'no'
            ]
        );

        $this->add_control(
            'subscriptions_per_page',
            [
                'label' => __('Subscriptions per Page', 'give'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('The number of subscriptions to show before pagination appears.', 'give'),
                'input_type' => 'number',
                'default' => '30'
            ]
        );

        $this->add_control(
            'give_subscriptions_info',
            [
                'label' => '',
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'content_classes' => 'give-info',
                'raw' => '
					<div class="give">
						<p class="info-head">
							' . __('GIVEWP SUBSCRIPTION HISTORY WIDGET', 'give') . '</p>
						<p class="info-message">' . __('This is the GiveWP Subscriptions widget. Choose which columns you want to have appear for your donors subscription history.', 'give') . '</p>
						<p class="give-docs-links">
							<a href="https://givewp.com/documentation/add-ons/recurring-donations/managing-subscriptions/?utm_source=plugin_settings&utm_medium=referral&utm_campaign=Free_Addons&utm_content=givelementor" rel="noopener noreferrer" target="_blank"><i class="fa fa-book" aria-hidden="true"></i>' . __('Visit the GiveWP Docs for more info on the GiveWP Subscriptions table.', 'give') . '</a>
						</p>
				</div>'
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render the [give_subscriptions] output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access protected
     */
    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $showStatus = ('yes' === $settings['show_status'] ? '' : 'show_status="false"');
        $showRenewalDate = ('yes' === $settings['show_renewal_date'] ? 'show_renewal_date="true"' : '');
        $showProgress = ('yes' === $settings['show_progress'] ? '' : 'show_progress="false"');
        $showStartDate = ('yes' === $settings['show_start_date'] ? '' : 'show_start_date="false"');
        $showEndDate = ('yes' === $settings['show_end_date'] ? 'show_end_date="true"' : '');
        $subscriptionsPerPage = ('yes' === $settings['subscriptions_per_page'] ? 'subscriptions_per_page="true"' : '');

        if (!\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            $html = do_shortcode(
                '
			[give_subscriptions '
                    . $showStatus . ' '
                    . $showRenewalDate . ' '
                    . $showProgress . ' '
                    . $showStartDate . ' '
                    . $showEndDate . ' '
                    . $subscriptionsPerPage .
                    ']'
            );
        } else {
            ob_start(); ?>
			<table id="give_user_history" class="give-table">
				<thead>
					<tr class="give_purchase_row">
							<th><?php _e('Subscription', 'give'); ?></th>
						<?php if ('yes' === $settings['show_status']) : ?>
							<th><?php _e('Status', 'give'); ?></th>
						<?php endif; ?>
						<?php if ('yes' === $settings['show_renewal_date']) : ?>
							<th><?php _e('Renewal Date', 'give'); ?></th>
						<?php endif; ?>
						<?php if ('yes' === $settings['show_progress']) : ?>
							<th><?php _e('Progress', 'give'); ?></th>
						<?php endif; ?>
						<?php if ('yes' === $settings['show_start_date']) : ?>
							<th><?php _e('Start Date', 'give'); ?></th>
						<?php endif; ?>
						<?php if ('yes' === $settings['show_end_date']) : ?>
							<th><?php _e('End Date', 'give'); ?></th>
						<?php endif; ?>
							<th><?php _e('Actions', 'give'); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<span class="give-subscription-name"><?php _e('Form with a Goal', 'give'); ?></span><br>
							<span class="give-subscription-billing-cycle">
								$25.00 / <?php _e('Monthly', 'give'); ?> </span>
						</td>
						<?php if ('yes' === $settings['show_status']) : ?>
						<td>
							<span class="give-subscription-status"><span class="give-donation-status status-active"><span class="give-donation-status-icon"></span> <?php _e('Active', 'give'); ?></span></span>
						</td>
						<?php endif; ?>
						<?php if ('yes' === $settings['show_renewal_date']) : ?>
						<td>
							<span class="give-subscription-renewal-date">
								<?php _e('Auto renew on June 4, 2020', 'give'); ?> </span>
						</td>
						<?php endif; ?>
						<?php if ('yes' === $settings['show_progress']) : ?>
						<td>
							<span class="give-subscription-times-billed">1 / <?php _e('Ongoing', 'give'); ?></span>
						</td>
						<?php endif; ?>
						<?php if ('yes' === $settings['show_start_date']) : ?>
						<td>
							<?php _e('May 4, 2020', 'give'); ?>
						</td>
						<?php endif; ?>
						<?php if ('yes' === $settings['show_end_date']) : ?>
						<td>
						<?php _e('Ongoing', 'give'); ?>
						</td>
						<?php endif; ?>
						<td>
							<a href="#"><?php _e('View Receipt', 'give'); ?></a>
							&nbsp;|&nbsp;
							<a href="#" class="give-cancel-subscription"><?php _e('Cancel', 'give'); ?></a>
						</td>
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
