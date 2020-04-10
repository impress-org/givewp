<?php

/**
 * Handle basic setup of form template
 *
 * @package Give
 * @since 2.7.0
 */

namespace Give\Form;

use Give\Form\Template\LegacyFormSettingCompatibility;
use Give\Form\Template\Options;
use function Give\Helpers\Form\Utils\createFailedPageURL;

defined( 'ABSPATH' ) || exit;

/**
 * Template class.
 *
 * @since 2.7.0
 */
abstract class Template {
	/**
	 * @var bool $openSuccessPageInIframe If set to false then success page will open in window instead of iframe.
	 */
	public $openSuccessPageInIframe = true;

	/**
	 * @var bool $openFailedPageInIframe If set to false then failed page will open in window instead of iframe.
	 */
	public $openFailedPageInIframe = true;

	/**
	 * @see src/Form/Template/LegacyFormSettingCompatibility.php:16 Check property description.
	 * @var array $defaultSettings Form settings default values for form template.
	 */
	protected $defaultLegacySettingValues = [];

	/**
	 * @see src/Form/Template/LegacyFormSettingCompatibility.php:18 Check property description.
	 * @var array $mapToLegacySetting
	 */
	protected $mapToLegacySetting = [];

	/**
	 * template vs file array
	 *
	 * @since 2.7.0
	 * @var array
	 */
	public $templates = [
		'form'                => GIVE_PLUGIN_DIR . 'src/Views/Form/defaultFormTemplate.php',
		'receipt'             => GIVE_PLUGIN_DIR . 'src/Views/Form/defaultFormReceiptTemplate.php',
		'donation-processing' => GIVE_PLUGIN_DIR . 'src/Views/Form/defaultFormDonationProcessing.php',
	];

	/**
	 * return form template ID.
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	abstract  public function getID();

	/**
	 * Get form template name.
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	abstract public function getName();

	/**
	 * Get form template image.
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
	 * Template template manager get template according to view.
	 * Note: Do not forget to call this function before close bracket in overridden getTemplate method
	 *
	 * @param string $template
	 *
	 * @return string
	 * @since 2.7.0
	 */
	public function getView( $template ) {
		return $this->templates[ $template ];
	}


	/**
	 * Get form template options
	 *
	 * @return Options
	 */
	public function getOptions() {
		return Options::fromArray( $this->getOptionsConfig() );
	}

	/**
	 * Get failed/cancelled donation message.
	 *
	 * @return string
	 * @since 2.7.0
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
