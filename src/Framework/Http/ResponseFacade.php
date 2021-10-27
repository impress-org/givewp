<?php

namespace Give\Framework\Http;

class ResponseFacade
{
	/**
	 * Send a JSON response
	 *
	 * @unreleased
	 *
	 * @param array|object $data Data to encode as JSON
	 * @param int|null $status_code The HTTP status code to output
	 * @param int|null $options Options to be passed to json_encode()
	 *
	 * @return void
	 */
	public function json($data = null, $status_code = null, $options = 0 )
	{
		wp_send_json( ['data' => $data], $status_code, $options );
	}

	/**
	 * Redirects elsewhere
	 *
	 * @unreleased
	 *
	 * @param string $location The path or URL to redirect to.
	 * @param int $status HTTP response status code to use. Default '302' (Moved Temporarily).
	 * @param string $x_redirect_by The application doing the redirect. Default 'WordPress'.
	 *
	 * @return void
	 */
	public function redirect($location, $status = 302, $x_redirect_by = 'WordPress')
	{
		wp_redirect($location, $status, $x_redirect_by);
		exit;
	}
}
