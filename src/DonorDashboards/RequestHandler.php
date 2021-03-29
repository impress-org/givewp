<?php

namespace Give\DonorDashboards;

use _WP_Dependency;
use Give\DonorDashboards\Admin\Settings;
use WP_Query;

/**
 * @since 2.10.0
 */
class RequestHandler {

	/**
	 * Register 'give-embed' query var
	 *
	 * @param array $vars
	 * @return array
	 */
	public function filterQueryVars( $vars ) {
		$vars[] = 'give-embed';
		$vars[] = 'give-generate-donor-dashboard-page';
		$vars[] = 'give-generated-donor-dashboard-page';
		return $vars;
	}

	/**
	 * Load donor dashboard markup, if donor dashboard exists in query vars
	 *
	 * @param WP_Query $query
	 * @return void
	 */
	public function parseRequest( $query ) {

		if ( is_admin() && current_user_can( 'manage_options' ) && ! wp_doing_ajax() && array_key_exists( 'give-generate-donor-dashboard-page', $query->query_vars ) ) {
			( new Settings() )->generateDonorDashboardPage();
			wp_safe_redirect( admin_url( 'edit.php?post_type=give_forms&page=give-settings&give-generated-donor-dashboard-page=1' ) );
			exit;
		}

		if ( array_key_exists( 'give-embed', $query->query_vars ) && $query->query_vars['give-embed'] === 'donor-dashboard' ) {

			$this->setUpFrontendHooks();

			$app = new App();
			echo $app->getIframeContent();
			exit; // and exit
		}

	}

	/**
	 * Setup frontend hooks
	 *
	 * @since 2.10.0
	 */
	public function setUpFrontendHooks() {
		add_action( 'give_embed_head', [ $this, 'noRobots' ] );
		add_action( 'give_embed_head', 'wp_enqueue_scripts', 1 );
		add_action( 'give_embed_head', [ $this, 'handleEnqueueScripts' ], 2 );
		add_action( 'give_embed_head', 'wp_print_styles', 8 );
		add_action( 'give_embed_head', 'wp_print_head_scripts', 9 );
		add_action( 'give_embed_footer', 'wp_print_footer_scripts', 20 );
	}

	/**
	 * Display a noindex meta tag.
	 *
	 * Outputs a noindex meta tag that tells web robots not to index and follow content.
	 *
	 * @since 2.10.0
	 */
	public function noRobots() {
		echo "<meta name='robots' content='noindex,nofollow'/>\n";
	}

	/**
	 * Handle enqueue script
	 *
	 * @since 2.10.0
	 */
	public function handleEnqueueScripts() {
		global $wp_scripts, $wp_styles;
		wp_enqueue_scripts();

		$wp_styles->dequeue( $this->getListOfScriptsToDequeue( $wp_styles->registered ) );
		$wp_scripts->dequeue( $this->getListOfScriptsToDequeue( $wp_scripts->registered ) );
	}

	/**
	 * Get filter list to dequeue scripts and style
	 *
	 * @param array $scripts
	 *
	 * @return array
	 * @since 2.10.0
	 */
	private function getListOfScriptsToDequeue( $scripts ) {
		$list     = [];
		$skip     = [ 'babel-polyfill' ];
		$themeDir = get_template_directory_uri();

		/* @var _WP_Dependency $data */
		foreach ( $scripts as $handle => $data ) {
			// Do not unset dependency.
			if ( in_array( $handle, $skip, true ) ) {
				continue;
			}

			// Do not allow styles and scripts from theme.
			if ( false !== strpos( (string) $data->src, $themeDir ) ) {
				$list[] = $handle;
				continue;
			}

			if (
				0 === strpos( $handle, 'give' ) ||
				false !== strpos( $data->src, '\give' )
			) {
				// Store dependencies to skip.
				$skip = array_merge( $skip, $data->deps );
				continue;
			}

			$list[] = $handle;
		}

		return $list;
	}
}
