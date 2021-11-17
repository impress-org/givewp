<?php

namespace Give\InPluginUpsells;

/**
 * @unreleased
 */
class SaleBanners
{
	/**
	 * @var string
	 */
	private $optionName = 'hidden-sale-banners';

	/**
	 * @var array
	 */
	private $hiddenBanners;

	public function __construct()
	{
		$this->hiddenBanners = give_get_option($this->optionName, []);
	}

	/**
	 * Get banners definitions
	 *
	 * @note id must be unique for each definition
	 *
	 * @return array[]
	 */
	public function getBanners()
	{
		return [
			[
				'id'              => 'giving_tuesday_2021',
				'accessibleLabel' => __('Giving Tuesday', 'give'),
				'leadText'        => __('Save 40% on all plans!', 'give'),
				'contentText'     => __('Our biggest sale of the year is here for a limited time only.', 'give'),
				'actionText'      => __('Shop Now', 'give'),
				'actionURL'       => 'https://givewp.com/sale',
				'startDate'       => strtotime('2021-11-14 00:00'),
				'endDate'         => strtotime('2021-11-17 24:00'),
			],
			[
				'id'              => 'banner2',
				'accessibleLabel' => __('Example Banner', 'give'),
				'leadText'        => __('Save 40% on all plans!', 'give'),
				'contentText'     => __('Our biggest sale of the year is here for a limited time only.', 'give'),
				'actionText'      => __('Shop Now', 'give'),
				'actionURL'       => 'https://givewp.com/sale',
				'startDate'       => strtotime('2021-11-17 00:00'),
				'endDate'         => strtotime('2021-11-23 24:00'),
			]
		];
	}

	/**
	 * Get the banners that should be displayed.
	 *
	 * @return array[]
	 */
	public function getVisibleBanners()
	{
		$currentDateTime = current_datetime()->getTimestamp();
		$currentUserId = get_current_user_id();

		return array_filter(
			$this->getBanners(),
			function ($banner) use ($currentDateTime, $currentUserId) {
				return true;
				$isHidden = in_array($banner['id'] . $currentUserId, $this->hiddenBanners);
				$isFuture = $currentDateTime >= $banner['startDate'];
				$isPast = $currentDateTime <= $banner['endDate'];

				return !($isHidden || $isFuture || $isPast);
			}
		);
	}


	/**
	 * @param string $banner
	 */
	public function hideBanner($banner)
	{
		$this->hiddenBanners[] = $banner;

		give_update_option(
			$this->optionName,
			array_unique($this->hiddenBanners)
		);
	}

	/**
	 * Render admin page
	 */
	public function render()
	{
		$banners = $this->getVisibleBanners();

		if (!empty($banners)) {
			include __DIR__ . '/resources/views/sale-banners.php';
		}
	}

	/**
	 * Load scripts
	 */
	public function loadScripts()
	{
		wp_enqueue_script(
			'give-in-plugin-upsells-sale-banners',
			GIVE_PLUGIN_URL . 'assets/dist/js/admin-upsell-sale-banner.js',
			[],
			GIVE_VERSION,
			true
		);

		wp_localize_script(
			'give-in-plugin-upsells-sale-banners',
			'GiveSaleBanners',
			[
				'apiRoot' => esc_url_raw(rest_url('give-api/v2/sale-banner')),
				'apiNonce' => wp_create_nonce('wp_rest'),
			]
		);

		wp_enqueue_style(
			'give-in-plugin-upsells-addons-font',
			'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap',
			[],
			null
		);
	}

	/**
	 * Helper function to determine if the current page Give admin page
	 *
	 * @return bool
	 */
	public static function isShowing()
	{
		return isset($_GET['post_type']) && $_GET['post_type'] === 'give_forms';
	}
}
