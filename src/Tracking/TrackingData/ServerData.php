<?php
namespace Give\Tracking\TrackingData;

use Give\Tracking\Contracts\TrackData;

/**
 * Class ServerData
 *
 * Represents the server data.
 *
 * @since 2.10.0
 * @package Give\Tracking\TrackingData
 */
class ServerData implements TrackData {

	/**
	 * Returns the collection data.
	 *
	 * @since 2.10.0
	 *
	 * @return array The collection data.
	 */
	public function get() {
		return $this->getServerData();
	}

	/**
	 * Returns the values with server details.
	 *
	 * @since 2.10.0
	 *
	 * @return array Array with the value.
	 */
	private function getServerData() {
		$server_data = [];

		// Validate if the server address is a valid IP-address.
		$ipaddress = filter_input( INPUT_SERVER, 'SERVER_ADDR', FILTER_VALIDATE_IP );
		if ( $ipaddress ) {
			$server_data['ip']       = $ipaddress;
			$server_data['Hostname'] = gethostbyaddr( $ipaddress );
		}

		$server_data['os']          = php_uname();
		$server_data['PhpVersion']  = PHP_VERSION;
		$server_data['CurlVersion'] = $this->getCurlInfo();

		return $server_data;
	}

	/**
	 * Returns details about the curl version.
	 *
	 * @since 2.10.0
	 *
	 * @return array|null The curl info. Or null when curl isn't available..
	 */
	private function getCurlInfo() {
		if ( ! function_exists( 'curl_version' ) ) {
			return null;
		}

		$curl = curl_version();

		$ssl_support = true;
		if ( ! $curl['features'] && CURL_VERSION_SSL ) {
			$ssl_support = false;
		}

		return [
			'version'    => $curl['version'],
			'sslSupport' => $ssl_support,
		];
	}
}

