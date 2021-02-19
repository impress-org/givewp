<?php

namespace Give\Tracking\TrackingData;


use Give\Tracking\Contracts\TrackData;

/**
 * Class WebsiteInfoData
 * @package Give\Tracking\TrackingData
 *
 * @since 2.10.0
 */
class WebsiteInfoData implements TrackData {

	/**
	 * @var ServerData
	 */
	private $serverData;

	/**
	 * @var WebsiteData
	 */
	private $websiteData;

	/**
	 * WebsiteInfoData constructor.
	 *
	 * @param  WebsiteData  $websiteData
	 * @param  ServerData  $serverData
	 */
	public function __construct( WebsiteData $websiteData, ServerData $serverData ) {
		$this->websiteData = $websiteData;
		$this->serverData  = $serverData;
	}

	/**
	 * @return array
	 */
	public function get() {
		return array_merge(
			$this->websiteData->get(),
			$this->serverData->get()
		);
	}
}
