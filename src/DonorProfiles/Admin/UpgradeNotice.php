<?php

namespace Give\DonorProfiles\Admin;

class UpgradeNotice {

	public function register() {
		if ( $this->shouldRenderOutput() ) {
			$this->renderOutput();
		}
	}

	protected function shouldRenderOutput() {
		return true;
	}

	protected function renderOutput() {
		echo $this->getOutput();
	}

	protected function getOutput() {
		ob_start();
		$output = '';
		require $this->getTemplatePath();
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	protected function getTemplatePath() {
		return GIVE_PLUGIN_DIR . '/src/DonorProfiles/resources/views/upgradenotice.php';
	}
}
