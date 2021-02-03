<?php

trait RestServer {

    /** @var WP_REST_Server */
    protected $server;

	protected function setupServer() {
		/** @var WP_REST_Server $wp_rest_server */
		global $wp_rest_server;
		$this->server = $wp_rest_server = new \WP_REST_Server;
		do_action( 'rest_api_init' );
	}
} 