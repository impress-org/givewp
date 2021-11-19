<?php

/**
 * Handle basic setup of form template
 *
 * @package Give
 * @since   2.7.0
 */

namespace Give\Form;

use Give\Form\Template\Options;
use Give\Helpers\Form\Utils as FormUtils;
use Give\Receipt\DonationReceipt;

defined('ABSPATH') || exit;

/**
 * Template class.
 *
 * @since 2.7.0
 */
abstract class Template
{
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
    public $donationFormLevelsStyle = 'buttons';

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
     * Get starting form height
     *
     * Returns starting height for iframe (in pixels), this is used to predict iframe height before the iframe loads
     * Implemented in includes/shortcodes.php:
     *
     * @param int $formId Form ID
     *
     * @return int
     **/
    public function getFormStartingHeight($formId)
    {
        return 600;
    }

    /**
     * Get form receipt height
     *
     * Returns receipt height for iframe (in pixels), this is used to predict iframe height before the iframe loads
     * Implemented in includes/shortcodes.php:
     * Implemented in form donation processing view
     *
     * @return int
     **/
    public function getFormReceiptHeight()
    {
        return 977;
    }

    /**
     * Get loading view filepath
     *
     * @since 2.7.0
     *
     * @return string
     */
    public function getLoadingView()
    {
        return GIVE_PLUGIN_DIR . 'src/Views/Form/defaultLoadingView.php';
    }

    /**
     * Renders the loading view
     *
     * @since 2.9.2
     *
     * @param int $formId
     */
    public function renderLoadingView($formId = null)
    {
        include $this->getLoadingView();
    }

    /**
     * Get form view filepath
     *
     * @since 2.7.0
     *
     * @return string
     */
    public function getFormView()
    {
        return GIVE_PLUGIN_DIR . 'src/Views/Form/defaultFormTemplate.php';
    }

    /**
     * Get receipt view filepath
     *
     * @since 2.7.0
     *
     * @return string
     */
    public function getReceiptView()
    {
        return GIVE_PLUGIN_DIR . 'src/Views/Form/defaultFormReceiptTemplate.php';
    }

    /**
     * return form template ID.
     *
     * @since 2.7.0
     * @return string
     */
    abstract public function getID();

    /**
     * Get form template name.
     *
     * @since 2.7.0
     * @return string
     */
    abstract public function getName();

    /**
     * Get form template image.
     *
     * @since 2.7.0
     * @return string
     */
    abstract public function getImage();

    /**
     * Get options config
     *
     * @since 2.7.0
     * @return array
     */
    abstract public function getOptionsConfig();

    /**
     * Get form template options
     *
     * @return Options
     */
    public function getOptions()
    {
        return Options::fromArray($this->getOptionsConfig());
    }

    /**
     * Get failed/cancelled donation message.
     *
     * @since 2.7.0
     * @return string
     */
    public function getFailedDonationMessage()
    {
        return esc_html__(
            'We\'re sorry, your donation failed to process. Please try again or contact site support.',
            'give'
        );
    }

    /**
     * Get failed donation page URL.
     *
     * @since 2.7.0
     *
     * @param int $formId
     *
     * @return mixed
     */
    public function getFailedPageURL($formId)
    {
        return FormUtils::createFailedPageURL(Give()->routeForm->getURL(get_post_field('post_name', $formId)));
    }

    /**
     * Get donate now button label text.
     *
     * @since 2.7.0
     * @return string
     */
    public function getDonateNowButtonLabel()
    {
        return __('Donate Now', 'give');
    }

    /**
     * Get continue to donation form button label text.
     *
     * @since 2.7.0
     * @return string
     */
    public function getContinueToDonationFormLabel()
    {
        return __('Donate Now', 'give');
    }

    /**
     * Get donation introduction text.
     *
     * @since 2.7.0
     * @return string|null
     */
    public function getDonationIntroductionContent()
    {
        return null;
    }

    /**
     * Return content position on donation form.
     *
     * Note: Even you are free to add introduction content at any place on donation form
     *       But still this depends upon form template style and configuration on which places you are allowed to show display introduction content.
     *
     * @since 2.7.0
     * @return string|null
     */
    public function getDonationIntroductionContentPosition()
    {
        return null;
    }

    /**
     * Get receipt details.
     *
     * @since 2.7.0
     *
     * @param int $donationId
     *
     * @return DonationReceipt
     */
    public function getReceiptDetails($donationId)
    {
        $receipt = new DonationReceipt($donationId);

        /**
         * Fire the action for receipt object.
         *
         * @since 2.7.0
         */
        do_action('give_new_receipt', $receipt);

        return $receipt;
    }

    /**
     * Get form heading
     *
     * @since 2.7.0
     *
     * @param int $formId
     *
     * @return string
     */
    public function getFormHeading($formId)
    {
        return get_the_title($formId);
    }

    /**
     * Get form image
     *
     * @since 2.7.0
     *
     * @param int $formId
     *
     * @return string
     */
    public function getFormFeaturedImage($formId)
    {
        return get_the_post_thumbnail_url($formId, 'full');
    }

    /**
     * Get form excerpt
     *
     * @since 2.7.0
     *
     * @param int|null $formId
     *
     * @return string
     */
    public function getFormExcerpt($formId)
    {
        return get_the_excerpt($formId);
    }
}
