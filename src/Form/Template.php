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
	 * Determines how the form is rendered to the page.
	 *
	 * Acceptable values:
	 *   - button (show a button that displays the form when clicked)
	 *   - onpage (render the form right on the page)
	 *
	 * @var string
	 */
	public $donationFormStyle = 'button';

	/**
	 * Determines how the form amount choices are rendered to the page.
	 *
	 * Acceptable values:
	 *   - buttons  (render donation amount choices as button )
	 *   - radio    (render donation amount choices as radio )
	 *   - dropdown (render donation amount choices in dropdown (select) )
	 *
	 * @var string
	 */
	public $donationFormLevelsStyle = 'button';

	/**
	 * Determines how the form field labels render on the page.
	 *
	 * Acceptable values:
	 *   - true   (render with floating label style)
	 *   - false  (render as is)
	 *
	 * @var bool
	 */
	public $floatingLabelsStyle = false;

	/**
	 * Determines whether or not render form content on page.
	 *
	 * Acceptable values:
	 *   - true  (render form content on page )
	 *   - false (do not render form content on page)
	 *
	 * @var bool
	 */
	public $showDonationIntroductionContent = false;

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
	 * Get donate now button label text.
	 *
	 * @since 2.7.0
	 * @return string
	 */
	public function getDonateNowButtonLabel() {
		return __( 'Donate Now', 'give' );
	}

	/**
	 * Get continue to donation form button label text.
	 *
	 * @since 2.7.0
	 * @return string
	 */
	public function getContinueToDonationFormLabel() {
		return __( 'Donate Now', 'give' );
	}

	/**
	 * Get donation introduction text.
	 *
	 * @since 2.7.0
	 * @return string|null
	 */
	public function getDonationIntroductionContent() {
		return null;
	}

	/**
	 * Return content position on donation form.
	 *
	 * Note: Even you are free to add introduction content at any place on donation form
	 *       But still this depends upon form template style and configuration on which places you are allowed to show display introduction content.
	 *
	 * @return string|null
	 * @since 2.7.0
	 */
	public function getDonationIntroductionContentPosition() {
		return null;
	}
}
