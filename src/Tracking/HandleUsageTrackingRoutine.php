<?php
namespace Give\Tracking;


use Give\Framework\Collector;
use Give\Tracking\TrackingData\PluginData;
use Give\Tracking\TrackingData\ServerData;
use Give\Tracking\TrackingData\SettingsData;
use Give\Tracking\TrackingData\ThemeData;
use Give\Tracking\TrackingData\WebsiteData;
use WP_Upgrader;

/**
 * Class HandleUsageTrackingRoutine
 *
 * This class handles the tracking routine.
 *
 * @since 2.10.0
 * @package Give\Tracking
 */
class HandleUsageTrackingRoutine {

	/**
	 * The tracking option name.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'give_usage_tracking_last_request';

	/**
	 * The limit for the option.
	 *
	 * @var int
	 */
	protected $threshold = WEEK_IN_SECONDS * 2;

	/**
	 * The endpoint to send the data to.
	 *
	 * @var string
	 */
	protected $endpoint = 'https//stats.givewp.com';

	/**
	 * The current time.
	 *
	 * @var int
	 */
	private $currentTime;

	/**
	 * HandleUsageTrackingRoutine constructor.
	 *
	 * @since 2.10.0
	 */
	public function __construct() {
		if ( ! $this->isTrackingEnabled() ) {
			return;
		}

		$this->currentTime = time();
	}

	/**
	 * Registers all hooks to WordPress.
	 */
	public function boot() {
		if ( ! $this->isTrackingEnabled() ) {
			return;
		}

		// Send tracking data on `admin_init`.
		add_action( 'admin_init', [ $this, 'send' ], 1 );

		// Add an action hook that will be triggered at the specified time by `wp_schedule_single_event()`.
		add_action( 'give_send_usage_tracking_data_after_core_update', [ $this, 'send' ] );

		// Call `wp_schedule_single_event()` after a WordPress core update.
		add_action( 'upgrader_process_complete', [ $this, 'scheduleTrackingDataSending' ], 10, 2 );

		// @todo send data when update givewp addon.
	}

	/**
	 * Schedules a new sending of the tracking data after a WordPress core update.
	 *
	 * @param  bool|WP_Upgrader  $upgrader  Optional. WP_Upgrader instance or false.
	 *                                   Depending on context, it might be a Theme_Upgrader,
	 *                                   Plugin_Upgrader, Core_Upgrade, or Language_Pack_Upgrader.
	 *                                   instance. Default false.
	 * @param  array  $data  Array of update data.
	 *
	 * @return void
	 */
	public function scheduleTrackingDataSending( $upgrader = false, $data = [] ) {
		// Return if it's not a WordPress core update.
		if ( ! $upgrader || ! isset( $data['type'] ) || $data['type'] !== 'core' ) {
			return;
		}

		/*
		 * To uniquely identify the scheduled cron event, `wp_next_scheduled()`
		 * needs to receive the same arguments as those used when originally
		 * scheduling the event otherwise it will always return false.
		 */
		if ( ! wp_next_scheduled( 'give_send_usage_tracking_data_after_core_update', true ) ) {
			/*
			 * Schedule sending of data tracking 6 hours after a WordPress core
			 * update. Pass a `true` parameter for the callback `$force` argument.
			 */
			wp_schedule_single_event( ( time() + ( HOUR_IN_SECONDS * 6 ) ), 'give_send_usage_tracking_data_after_core_update', true );
		}
	}

	/**
	 * Sends the tracking data.
	 *
	 * @param  bool  $force  Whether to send the tracking data ignoring the two
	 *                    weeks time threshold. Default false.
	 */
	public function send( $force = false ) {
		if ( ! $this->shouldSendTracking( $force ) ) {
			return;
		}

		// Set a 'content-type' header of 'application/json'.
		$tracking_request_args = [
			'headers'     => [ 'content-type:' => 'application/json' ],
			'timeout'     => 8,
			'httpversion' => '1.1',
			'blocking'    => false,
			'user-agent'  => 'GIVE/' . GIVE_VERSION . ' ' . get_bloginfo( 'url' ),
			'body'        => $this->getCollector()->getAsJson(),
			'data_format' => 'body',
		];

		wp_remote_post( $this->endpoint, $tracking_request_args );

		update_option( self::OPTION_NAME, $this->currentTime );
	}

	/**
	 * Determines whether to send the tracking data.
	 *
	 * Returns false if tracking is disabled or the current page is one of the
	 * admin plugins pages. Returns true when there's no tracking data stored or
	 * the data was sent more than two weeks ago. The two weeks interval is set
	 * when instantiating the class.
	 *
	 * @param  bool  $ignore_time_threshold  Whether to send the tracking data ignoring
	 *                                    the two weeks time threshold. Default false.
	 *
	 * @return bool True when tracking data should be sent.
	 */
	protected function shouldSendTracking( $ignore_time_threshold = false ) {
		// Only send tracking on the main site of a multi-site instance. This returns true on non-multisite installs.
		if ( ! is_main_site() ) {
			return false;
		}

		$lastTime = get_option( self::OPTION_NAME );

		// When tracking data haven't been sent yet or when sending data is forced.
		if ( ! $lastTime || $ignore_time_threshold ) {
			return true;
		}

		return $this->exceedsThreshold( $this->currentTime - $lastTime );
	}

	/**
	 * Checks if the given amount of seconds exceeds the set threshold.
	 *
	 * @param  int  $seconds  The amount of seconds to check.
	 *
	 * @return bool True when seconds is bigger than threshold.
	 */
	protected function exceedsThreshold( $seconds ) {
		return ( $seconds > $this->threshold );
	}

	/**
	 * Returns the collector for collecting the data.
	 *
	 * @return Collector The instance of the collector.
	 */
	public function getCollector() {
		/* @var Collector $collector */
		$collector = give( Collector::class );

		$collector->addCollection( WebsiteData::class );
		$collector->addCollection( ServerData::class );
		$collector->addCollection( ThemeData::class );
		$collector->addCollection( PluginData::class );
		$collector->addCollection( SettingsData::class );

		return $collector;
	}

	/**
	 * See if we should run tracking at all.
	 *
	 * @return bool True when we can track, false when we can't.
	 */
	private function isTrackingEnabled() {
		if (
			function_exists( 'wp_get_environment_type' ) &&
			wp_get_environment_type() !== 'production'
		) {
			return false;
		}

		// Check if we're allowing tracking.
		$tracking = give_get_option( AdminSettings::USAGE_TRACKING_OPTION_NAME );

		return give_is_setting_enabled( $tracking );
	}
}
