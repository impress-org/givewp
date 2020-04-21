<?php
/**
 * Handle iframe skin.
 *
 * @package Give
 */

namespace Give\Views;

use Give\Form\Template;
use function Give\Helpers\Form\Utils\getSuccessPageURL;
use function Give\Helpers\Form\Utils\isViewingFormReceipt;
use function Give\Helpers\removeDonationAction;

/**
 * Class IframeView
 *
 * Note: only for internal use.
 *
 * @since   2.7.0
 * @package Give
 */
class IframeView {
	/**
	 * Iframe URL.
	 *
	 * This will be use to setup src attribute.
	 *
	 * @since 2.7.0
	 * @var string
	 */
	protected $url;

	/**
	 * Flag to check whether show modal or iframe on page.
	 *
	 * @since 2.7.0
	 * @var bool
	 */
	protected $modal = false;

	/**
	 * Flag to check whether on not auto scroll page to iframe.
	 *
	 * @since 2.7.0
	 * @var int
	 */
	protected $autoScroll = 0;

	/**
	 * Iframe minimum height.
	 *
	 * @since 2.7.0
	 * @var bool
	 */
	protected $minHeight = null;

	/**
	 * Unique identifier.
	 *
	 * @var string|null
	 */
	protected $uniqueId = null;

	/**
	 * Button title.
	 *
	 * @var string|null
	 */
	protected $buttonTitle = null;

	/**
	 * Button color.
	 *
	 * @var string|null
	 */
	protected $buttonColor = null;

	/**
	 * Form template.
	 *
	 * @var Template
	 */
	protected $template = null;

	/**
	 * Form id.
	 *
	 * @var int
	 */
	protected $formId = 0;

	/**
	 * IframeView Constructor
	 *
	 * @param Template $template
	 */
	public function __construct( $template = null ) {
		$this->uniqueId    = uniqid( 'give-' );
		$this->buttonTitle = __( 'Click to donate', 'give' );
	}

	/**
	 * Set iframe URL.
	 *
	 * @param string $url
	 *
	 * @return $this
	 */
	public function setURL( $url = null ) {
		$this->url = add_query_arg(
			array_merge( [ 'giveDonationFormInIframe' => 1 ] ),
			$url
		);

		return $this;
	}

	/**
	 * Set whether or not show modal.
	 *
	 * @param bool $set
	 *
	 * @return $this
	 */
	public function showInModal( $set ) {
		$this->modal = $set;

		return $this;
	}

	/**
	 * Button title.
	 *
	 * @param string $title
	 *
	 * @return $this
	 */
	public function setButtonTitle( $title ) {
		$this->buttonTitle = $title;

		return $this;
	}

	/**
	 * Button color.
	 *
	 * @param string $color
	 *
	 * @return $this
	 */
	public function setButtonColor( $color ) {
		$this->buttonColor = $color;

		return $this;
	}

	/**
	 * Form id.
	 *
	 * @param int $id
	 *
	 * @return $this
	 */
	public function setFormId( $id ) {
		$this->formId = $id;

		return $this;
	}

	/**
	 * Get iframe HTML.
	 *
	 * @return string
	 */
	private function getIframeHTML() {
		$iframe = sprintf(
			'<iframe
						name="give-embed-form"
						%1$s
						%4$s
						data-autoScroll="%2$s"
						onload="Give.initializeIframeResize(this)"
						style="border: 0;visibility: hidden;%3$s"></iframe>',
			$this->modal ? "data-src=\"{$this->url}\"" : "src=\"{$this->url}\"",
			$this->modal ? 0 : $this->autoScroll,
			$this->minHeight ? "min-height: {$this->minHeight}px;" : '',
			$this->modal ? 'class="in-modal"' : ''
		);

		if ( $this->modal ) {
			$iframe = sprintf(
				'<div class="modal-inner-wrap">
					<div class="modal-content">
						%1$s
						<button class="close-btn js-give-embed-form-modal-closer" aria-label="%2$s" data-form-id="%3$s">&times;</button>
					</div>
				</div>
				',
				$iframe,
				__( 'Close modal', 'give' ),
				$this->uniqueId
			);
		}

		return $iframe;
	}


	/**
	 * Get button HTML.
	 *
	 * @return string
	 */
	private function getButtonHTML() {
		return sprintf(
			'<div>
						<button
						type="button"
						class="js-give-embed-form-modal-opener"
						data-form-id="%1$s"%3$s>%2$s</button>
					</div>',
			$this->uniqueId,
			$this->buttonTitle,
			$this->buttonColor ? " style=\"background-color: {$this->buttonColor}\"" : ''
		);
	}

	/**
	 * Get iframe URL.
	 *
	 * @return string
	 */
	private function getIframeURL() {
		$query_string           = array_map( 'give_clean', wp_parse_args( $_SERVER['QUERY_STRING'] ) );
		$donation_history       = give_get_purchase_session();
		$hasAction              = ! empty( $query_string['giveDonationAction'] );
		$this->autoScroll       = absint( $hasAction );
		$donationFormHasSession = $this->formId === absint( $donation_history['post_data'] ['give-form-id'] );

		// Do not pass donation acton by query param if does not belong to current form.
		if (
			$hasAction &&
			! empty( $donation_history ) &&
			! $donationFormHasSession
		) {
			unset( $query_string['giveDonationAction'] );
			$hasAction        = false;
			$this->autoScroll = 0;
		}

		// Build iframe url.
		$url = Give()->routeForm->getURL( get_post_field( 'post_name', $this->formId ) );

		if ( ( $hasAction && 'showReceipt' === $query_string['giveDonationAction'] ) || isViewingFormReceipt() ) {
			$url = getSuccessPageURL();

		} elseif ( ( $hasAction && 'failedDonation' === $query_string['giveDonationAction'] ) ) {
			$url                                     = $this->template->getFailedPageURL( $this->formId );
			$query_string['showFailedDonationError'] = 1;
		}

		$iframe_url = add_query_arg(
			array_merge( [ 'giveDonationFormInIframe' => 1 ], $query_string ),
			trailingslashit( $url )
		);

		return removeDonationAction( $iframe_url );
	}

	/**
	 *  Setup Default config.
	 */
	private function loadDefaultConfig() {
		$this->template  = Give()->templates->getTemplate();
		$this->minHeight = $this->template->getFormStartingHeight();

		$this->url = $this->url ?: $this->getIframeURL();
	}

	/**
	 * Render view.
	 *
	 * Note: if you want to overwrite this function then do not forget to add action hook in footer and header.
	 * We use these hooks to manipulated donation form related actions.
	 *
	 * @since 2.7.0
	 */
	public function render() {
		ob_start();

		$this->loadDefaultConfig();

		if ( $this->modal ) {
			echo $this->getButtonHTML();
		}

		printf(
			'<div class="give-embed-form-wrapper%1$s" id="%2$s">%3$s<div class="iframe-loader">',
			$this->modal ? ' is-hide' : '',
			$this->uniqueId,
			$this->getIframeHTML()
		);

		include $this->template->getLoadingView();

		echo '</div></div>';

		return ob_get_clean();
	}
}
