<?php

namespace Give\DonorDashboards\Admin;

class SuccessNotice {

	/**
	 * Reigster success notice
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
		return isset( $_GET['give-generated-donor-dashboard-page'] );
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
		return GIVE_PLUGIN_DIR . '/src/DonorDashboards/resources/views/successnotice.php';
	}
}
