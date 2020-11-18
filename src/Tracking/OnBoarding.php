<?php
namespace Give\Tracking;

/**
 * Class OnBoarding
 * @package Give\Tracking
 *
 * This class uses to setup notice nag to website administrator if admin is not opt in for usage tracking.
 *
 * @since 2.10.0
 */
class OnBoarding {
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
		if ( give_is_setting_enabled( give_get_option( 'usage_tracking', 'disabled' ) ) ) {
			return;
		}

		$notice       = esc_html__( 'You can contribute to improve GiveWP. If you opt-in to "Usage Tracking" then we will track non-sensitive data of your website. We will use this information to improve plugin.', 'give' );
		$readMoreLink = sprintf(
			'<a href="#" target="_blank">%1$s</a>',
			esc_html__( 'Read more GiveWP.com Usage Tracking.', 'give' )
		);

		$allowTrackingLink = sprintf(
			'<br><br><a href="%3$s" target="_blank" class="button-secondary">%1$s</a>&nbsp;&nbsp;<a href="%4$s" target="_blank" class="button-secondary">%2$s</a>',
			esc_html__( 'Allow', 'give' ),
			esc_html__( 'Do not allow', 'give' ),
			add_query_arg( [ 'give_action' => 'opt_in_into_tracking' ] ),
			add_query_arg( [ 'give_action' => 'opt_out_into_tracking' ] )
		);

		give()->notices->register_notice(
			[
				'id'               => 'usage-tracking-nag',
				'description'      => "{$notice} {$readMoreLink} {$allowTrackingLink}",
				'dismiss_interval' => 'dismiss_interval',
			]
		);
	}
}
