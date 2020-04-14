<?php

/**
 * Handle basic setup of form template
 *
 * @package Give
 * @since   2.7.0
 */

namespace Give\Form;

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
	 * Flag to check whether or not open success page in iframe.
	 *
	 * @var bool $openSuccessPageInIframe If set to false then success page will open in window instead of iframe.
	 */
	public $openSuccessPageInIframe = true;

	/**
	 * Flag to check whether or not open failed page in iframe.
	 *
	 * @var bool $openFailedPageInIframe If set to false then failed page will open in window instead of iframe.
	 */
	public $openFailedPageInIframe = true;

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
	 * @return string
	 * @since 2.7.0
	 */
	abstract public function getID();

	/**
	 * Get form template name.
	 *
	 * @return string
	 * @since 2.7.0
	 */
	abstract public function getName();

	/**
	 * Get form template image.
	 *
	 * @return string
	 * @since 2.7.0
	 */
	abstract public function getImage();

	/**
	 * Get options config
	 *
	 * @return array
	 * @since 2.7.0
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
	 * @return mixed
	 * @since 2.7.0
	 */
	public function getFailedPageURL( $formId ) {
		return createFailedPageURL( Give()->routeForm->getURL( get_post_field( 'post_name', $formId ) ) );
	}

	/**
	 * Get translated strings.
	 *
	 * @since 2.7.0
	 *
	 * @return array
	 */
	public function getTranslatedStrings() {
		return [
			'donateNowButtonLabel'        => __( 'Donate Now', 'give' ),
			'continueToDonationFormLabel' => __( 'Donate Now', 'give' ),
		];
	}


	/**
	 * Get translated string.
	 *
	 * @param string $id String Id.
	 *
	 * @return string
	 */
	public function getTranslatedString( $id ) {
		$strings = $this->getTranslatedStrings();

		return array_key_exists( $id, $strings ) ? $strings['$id'] : '';
	}

	/**
	 * Get donate now button label text.
	 *
	 * @since 2.7.0
	 * @return string
	 */
	public function getDonateNowButtonLabel() {
		return $this->getTranslatedString( 'donateNowButtonLabel' );
	}

	/**
	 * Get continue to donation form button label text.
	 *
	 * @since 2.7.0
	 * @return string
	 */
	public function getContinueToDonationFormLabel() {
		return $this->getTranslatedString( 'continueToDonationFormLabel' );
	}

	/**
	 * Get donation introduction text.
	 *
	 * @since 2.7.0
	 * @return string
	 */
	public function getDonationIntroductionContent() {
		return '';
	}

	/**
	 * Return donation form display style.
	 *
	 * Note: only onpage or button value will be accepted.
	 */
	public function getDonationFormDisplayStyle() {
		return 'button';
	}

	/**
	 * Return donation form level display style.
	 * Donation levels only exist if form is multiple donation type in which you have choice to choose between multiple amounts.
	 *
	 * Note: only buttons, radio or dropdown value will ve accepted.
	 */
	public function getDonationLevelsDisplayStyle() {
		return 'buttons';
	}

	/**
	 * Return content position on donation form.
	 *
	 * Note: Even you are free to add introduction content at any place on donation form
	 *       But still this depends upon form template style and configuration on which places you are allowed to show display introduction content.
	 *
	 * @return string
	 * @since 2.7.0
	 */
	public function getDonationIntroductionContentPosition() {
		return '';
	}

	/**
	 * Return true or false to activate floating label on donation form.
	 *
	 * Note: This function returned value will decide to load float-label related style and script on donation form.
	 *
	 * @see https://pryley.github.io/float-labels.js/
	 *
	 * @since 2.7.0
	 * @return bool
	 */
	public function isShowFloatingLabels() {
		return false;
	}

	/**
	 * Return true or false to show  or hide donation introduction content.
	 *
	 * @since 2.7.0
	 *
	 * @return bool
	 */
	public function isShowDonationIntroductionContent() {
		return false;
	}
}
