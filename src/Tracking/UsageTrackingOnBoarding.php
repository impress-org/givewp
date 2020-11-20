<?php
namespace Give\Tracking;

/**
 * Class OnBoarding
 * @package Give\Tracking
 *
 * This class uses to setup notice nag to website administrator if admin is not opt in for usage tracking and gives admin an option to directly opt-in.
 *
 * @since 2.10.0
 */
class UsageTrackingOnBoarding {
	/**
	 * Bootstrap
	 *
	 * @since 2.10.0
	 */
	public function boot() {
		add_action( 'admin_notices', [ $this, 'addNotice' ] );
	}

	/**
	 * Register notice.
	 *
	 * @sicne 2.10.0
	 */
	public function addNotice() {
		$isAdminOptedIn = give_is_setting_enabled( give_get_option( AdminSettings::USAGE_TRACKING_OPTION_NAME, 'disabled' ) );

		if ( $isAdminOptedIn || ! current_user_can( 'manage_give_settings' ) ) {
			return;
		}

		$notice = sprintf(
			'<strong>%1$s</strong><br><i>%2$s</i>',
			esc_html__( 'Can GiveWP collect data about the usage of the plugin?', 'give' ),
			esc_html__( 'Usage data is completely anonymous, does not include any personal information, and will only be used to improve the software.', 'give' )
		);

		$allowTrackingLink = sprintf(
			'<br><br><a href="%3$s" class="button-secondary">%1$s</a>&nbsp;&nbsp;<a href="%4$s" class="button-secondary">%2$s</a>',
			esc_html__( 'Yes', 'give' ),
			esc_html__( 'No', 'give' ),
			add_query_arg( [ 'give_action' => 'opt_in_into_tracking' ] ),
			add_query_arg( [ 'give_action' => 'opt_out_into_tracking' ] )
		);

		give()->notices->register_notice(
			[
				'id'               => 'usage-tracking-nag',
				'type'             => 'info',
				'description'      => "{$notice}{$allowTrackingLink}",
				'dismissible_type' => 'user',
				'dismiss_interval' => 'shortly',
			]
		);
	}

	/**
	 * Get option name of notice.
	 *
	 * We use this option key to disable notice nag for specific user for a interval.
	 *
	 * @since 2.10.0
	 * @return string
	 */
	public function getNoticeOptionKey() {
		return give()->notices->get_notice_key( 'usage-tracking-nag', 'shortly', wp_get_current_user()->ID );
	}
}
