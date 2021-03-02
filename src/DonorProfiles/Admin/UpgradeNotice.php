<?php

namespace Give\DonorProfiles\Admin;

class UpgradeNotice {

	/**
	 * Reigster upgrade notice
	 *
	 * @return void
	 *
	 * @since 2.10.0
	 */
	public function register() {
		if ( $this->shouldRenderOutput() ) {
			$this->renderOutput();
		}
	}

	/**
	 * Return true if notice should be rendered, false if not
	 *
	 * @return boolean
	 *
	 * @since 2.10.0
	 */
	protected function shouldRenderOutput() {

		// Give Admin Only.
		if ( give_is_admin_page() ) {

			$donorProfilePageIsSet = empty( give_get_option( 'donor_profile_page' ) ) || get_post_status( give_get_option( 'donor_profile_page' ) ) === false ? false : true;
			$historyPageIsSet      = empty( give_get_option( 'history_page' ) ) ? false : true;

			if ( $donorProfilePageIsSet === false && $historyPageIsSet === true ) {
				return true;
			} else {
				return false;
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Render notice output
	 *
	 * @return void
	 *
	 * @since 2.10.0
	 */
	protected function renderOutput() {
		echo $this->getOutput();
	}

	/**
	 * Get notice output
	 *
	 * @return string
	 *
	 * @since 2.10.0
	 */
	protected function getOutput() {
		ob_start();
		$output = '';
		require $this->getTemplatePath();
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	/**
	 * Get template path for notice output
	 *
	 * @return string
	 *
	 * @since 2.10.0
	 */
	protected function getTemplatePath() {
		return GIVE_PLUGIN_DIR . '/src/DonorProfiles/resources/views/upgradenotice.php';
	}
}
