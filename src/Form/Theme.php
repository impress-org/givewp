<?php

/**
 * Handle Theme registration
 *
 * @package Give
 * @since 2.7.0
 */

namespace Give\Form;

use Give\Form\Theme\LegacyFormSettingCompatibility;
use Give\Form\Theme\Options;
use function Give\Helpers\Form\Utils\createFailedPageURL;

defined( 'ABSPATH' ) || exit;

/**
 * Theme class.
 *
 * @since 2.7.0
 */
abstract class Theme {

	/**
	 * @var bool $openSuccessPageInIframe If set to false then success page will open in window instead of iframe.
	 */
	public $openSuccessPageInIframe = true;

	/**
	 * @var bool $openFailedPageInIframe If set to false then failed page will open in window instead of iframe.
	 */
	public $openFailedPageInIframe = true;

	/**
	 * @see src/Form/Theme/LegacyFormSettingCompatibility.php:16 Check property description.
	 * @var array $defaultSettings Form settings default values for form template.
	 */
	protected $defaultLegacySettingValues = [];

	/**
	 * @see src/Form/Theme/LegacyFormSettingCompatibility.php:18 Check property description.
	 * @var array $mapToLegacySetting
	 */
	protected $mapToLegacySetting = [];

	/**
	 * Get starting form height
	 *
	 * Returns starting height for iframe (in pixels), this is used to predict iframe height before the iframe loads
	 * Implemented in includes/shortcodes.php:
	 *
	 * @return int
	 **/
	public function getFormStartingHeight() {
		return 600;
	}

	/**
	 * Get loading view filepath
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	public function getLoadingView() {
		return GIVE_PLUGIN_DIR . 'src/Views/Form/defaultLoadingView.php';
	}

	/**
	 * Get form view filepath
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	public function getFormView() {
		return GIVE_PLUGIN_DIR . 'src/Views/Form/defaultFormTemplate.php';
	}

	/**
	 * Get receipt view filepath
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	public function getReceiptView() {
		return GIVE_PLUGIN_DIR . 'src/Views/Form/defaultFormReceiptTemplate.php';
	}

	/**
	 * Get donation processing view filepath
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	public function getDonationProcessingView() {
		return GIVE_PLUGIN_DIR . 'src/Views/Form/defaultFormDonationProcessing.php';
	}



	/**
	 * return theme ID.
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	abstract  public function getID();

	/**
	 * Get theme name.
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	abstract public function getName();

	/**
	 * Get theme image.
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	abstract public function getImage();

	/**
	 * Get options config
	 *
	 * @since 2.7.0
	 *
	 * @return array
	 */
	abstract public function getOptionsConfig();

	/**
	 * Get theme options
	 *
	 * @return Options
	 */
	public function getOptions() {
		return Options::fromArray( $this->getOptionsConfig() );
	}

	/**
	 * Get failed/cancelled donation message.
	 *
	 * @since 2.7.0
	 * @return string
	 */
	public function getFailedDonationMessage() {
		return esc_html__( 'We\'re sorry, your donation failed to process. Please try again or contact site support.', 'give' );
	}


	/**
	 * Get failed donation page URL.
	 *
	 * @param int $formId
	 *
	 * @since 2.7.0
	 * @return mixed
	 */
	public function getFailedPageURL( $formId ) {
		return createFailedPageURL( Give()->routeForm->getURL( get_post_field( 'post_name', $formId ) ) );
	}


	/**
	 * Returns LegacyFormSettingCompatibility object.
	 *
	 * This function helps to maintain backward compatibility with legacy form settings.
	 *
	 * @since 2.7.0
	 *
	 * @return LegacyFormSettingCompatibility|null
	 */
	public function getLegacySettingHandler() {
		return $this->mapToLegacySetting || $this->defaultLegacySettingValues ?
			new LegacyFormSettingCompatibility( $this->mapToLegacySetting, $this->defaultLegacySettingValues ) :
			null;
	}
}
