<?php

namespace Give\Campaigns;

class AdminPageView {

	public function __construct( $templatePath ) {
		$this->templatePath = $templatePath;
	}

	public function render() {
		ob_start();
		include $this->templatePath . 'submenu.php';
		echo ob_get_clean();
	}
}
