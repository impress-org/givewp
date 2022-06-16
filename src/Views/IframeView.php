<?php
/**
 * Handle iframe skin.
 *
 * @package Give
 */

namespace Give\Views;

use Give\Form\Template;
use Give\Helpers\Form\Template as FormTemplateUtils;
use Give\Helpers\Form\Utils as FormUtils;
use Give\Helpers\Utils as GlobalUtils;

/**
 * Class IframeView
 *
 * Note: only for internal use.
 *
 * @package Give
 * @since   2.7.0
 */
class IframeView
{
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
    public function __construct($template = null)
    {
        $this->uniqueId = uniqid('give-');
        $this->buttonTitle = esc_html__('Click to donate', 'give');
    }

    /**
     * Set iframe URL.
     *
     * @param string $url
     *
     * @return $this
     */
    public function setURL($url = null)
    {
        $this->url = esc_url(
            add_query_arg(
                ['giveDonationFormInIframe' => 1],
                $url
            )
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
    public function showInModal($set)
    {
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
    public function setButtonTitle($title)
    {
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
    public function setButtonColor($color)
    {
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
    public function setFormId($id)
    {
        $this->formId = $id;

        return $this;
    }

    /**
     * Return whether or not rest api request to render donation form block.
     *
     * @since 2.7.0
     * @return bool
     */
    private function isDonationFormBlockRendererApiRequest()
    {
        return false !== strpos(
                $_SERVER['REQUEST_URI'],
                rest_get_url_prefix() . '/wp/v2/block-renderer/give/donation-form'
            );
    }

    /**
     * Extra extra query param to iframe url.
     *
     * @since 2.7.0
     */
    private function addExtraQueryParams()
    {
        // We can prevent live donation on in appropriate situation like: previewing donation form (with draft status)
        if (FormTemplateUtils\Utils\Frontend::getPreviewDonationFormId(
            ) || $this->isDonationFormBlockRendererApiRequest()) {
            $this->url = esc_url(
                add_query_arg(
                    ['giveDisableDonateNowButton' => 1],
                    $this->url
                )
            );
        }
    }

    /**
     * Get iframe HTML.
     *
     * @return string
     */
    private function getIframeHTML()
    {
        ob_start();

        $this->template->renderLoadingView($this->formId);

        $loader = sprintf(
            '<div class="iframe-loader">%1$s</div>',
            ob_get_clean()
        );

        $iframe = sprintf(
            '<iframe
				name="give-embed-form"
				%1$s
				%4$s
				data-autoScroll="%2$s"
				onload="if( \'undefined\' !== typeof Give ) { Give.initializeIframeResize(this) }"
				style="border: 0;visibility: hidden;%3$s"></iframe>%5$s',
            $this->modal ? "data-src=\"{$this->url}\"" : "src=\"{$this->url}\"",
            $this->autoScroll,
            $this->minHeight ? "min-height: {$this->minHeight}px;" : '',
            $this->modal ? 'class="in-modal"' : '',
            $loader
        );

        if ($this->modal) {
            $iframe = sprintf(
                '<div class="modal-inner-wrap">
					<div class="modal-content">
		    			<a href="#" class="close-btn js-give-embed-form-modal-closer" aria-label="%3$s" data-form-id="%3$s" rel="nofollow">%2$s<span>&times;</span></a>
						%1$s
					</div>
				</div>',
                $iframe,
                esc_html__('Close', 'give'),
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
    private function getButtonHTML()
    {
        return sprintf(
            '<div class="js-give-embed-form-modal-launcher-wrap">
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
    private function getIframeURL()
    {
        $query_string = array_map('give_clean', wp_parse_args($_SERVER['QUERY_STRING']));
        $donationHistory = give_get_purchase_session();
        $hasAction = !empty($query_string['giveDonationAction']);
        $this->autoScroll = absint($hasAction);
        $donationFormHasSession = null;

        if ($donationHistory) {
            $donationFormHasSession = $this->formId === absint($donationHistory['post_data'] ['give-form-id']);
        }

        // Do not pass donation acton by query param if does not belong to current form.
        if (
            $hasAction &&
            !empty($donationHistory) &&
            !$donationFormHasSession
        ) {
            unset($query_string['giveDonationAction']);
            $hasAction = false;
            $this->autoScroll = 0;
        }

        // Build iframe url.
        $url = Give()->routeForm->getURL(get_post_field('post_name', $this->formId));

        if (($hasAction && 'showReceipt' === $query_string['giveDonationAction']) || FormUtils::isViewingFormReceipt(
            )) {
            $url = FormUtils::getSuccessPageURL();
        } elseif (($hasAction && 'failedDonation' === $query_string['giveDonationAction'])) {
            $url = $this->template->getFailedPageURL($this->formId);
            $query_string['showFailedDonationError'] = 1;
        }

        $iframe_url = add_query_arg(
            array_merge(['giveDonationFormInIframe' => 1], $query_string),
            $url
        );

        return GlobalUtils::removeDonationAction($iframe_url);
    }

    /**
     *  Setup Default config.
     */
    private function loadDefaultConfig()
    {
        $activeFormTemplate = FormTemplateUtils::getActiveID($this->formId);
        $this->template = Give()->templates->getTemplate($activeFormTemplate);
        $this->minHeight = $this->template->getFormStartingHeight($this->formId);

        $this->url = $this->url ?: $this->getIframeURL();

        $this->addExtraQueryParams();
    }

    /**
     * Render view.
     *
     * Note: if you want to overwrite this function then do not forget to add action hook in footer and header.
     * We use these hooks to manipulated donation form related actions.
     *
     * @since 2.7.0
     */
    public function render()
    {
        ob_start();

        $this->loadDefaultConfig();

        if ($this->modal) {
            echo $this->getButtonHTML();
        }

        printf(
            '<div class="give-embed-form-wrapper%1$s" id="%2$s">%3$s</div>',
            $this->modal ? ' is-hide' : '',
            $this->uniqueId,
            $this->getIframeHTML()
        );

        return ob_get_clean();
    }
}
