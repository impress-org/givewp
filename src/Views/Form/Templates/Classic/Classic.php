<?php

namespace Give\Views\Form\Templates\Classic;

use Give\Form\Template;
use Give\Form\Template\Hookable;
use Give\Form\Template\Scriptable;
use Give\Helpers\Form\Template as FormTemplateUtils;
use Give\Receipt\DonationReceipt;
use InvalidArgumentException;

/**
 * Classic Donation Form Template
 *
 * @unreleased
 */
class Classic extends Template implements Hookable, Scriptable
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var bool
     */
    private $scriptsLoaded = false;

    public function __construct()
    {
        $this->options = FormTemplateUtils::getOptions();
    }

    /**
     * @inheritDoc
     */
    public function getID()
    {
        return 'classic';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return __('Classic Donation Form', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getImage()
    {
        return GIVE_PLUGIN_URL . 'assets/dist/images/admin/ClassicForm.jpg';
    }

    /**
     * @inheritDoc
     */
    public function getOptionsConfig()
    {
        return require 'optionConfig.php';
    }

    /**
     * @inheritDoc
     */
    public function loadHooks()
    {
        add_action('give_pre_form', [ $this, 'renderIconDefinitions' ]);

        // Display header
        if ('enabled' === $this->options[ 'appearance' ][ 'display_header' ]) {
            add_action('give_pre_form', [ $this, 'renderHeader' ]);
        }

        add_action('give_before_donation_levels', [ $this, 'renderDonationAmountHeading' ], 20);

        $sections = [
            [
                'hooks' => [ 'give_donation_form_top' ],
                'class' => 'give-donation-amount-section',
            ],
            [
                'hooks' => [ 'give_donation_form_register_login_fields' ],
                'class' => 'give-personal-info-section',
            ],
            [
                'hooks' => [ 'give_payment_mode_top', 'give_payment_mode_bottom' ],
                'class' => 'give-payment-details-section',
            ],
            [
                'hooks' => [ 'give_donation_form_before_submit', 'give_donation_form_after_submit' ],
                'class' => 'give-donate-now-button-section',
            ],
        ];

        foreach ($sections as $section) {
            list ( $start, $end ) = array_pad($section[ 'hooks' ], 2, null);

            add_action($start, function () use ($section) {
                printf('<section class="give-form-section %s">', $section[ 'class' ]);
            }, -10000);

            add_action($end ?: $start, function () {
                echo '</section>';
            }, 10000);
        }

        /**
         * Remove actions
         */
        // Remove goal.
        remove_action('give_pre_form', 'give_show_goal_progress');
        // Remove intermediate continue button which appear when display style set to other then onpage.
        remove_action('give_after_donation_levels', 'give_display_checkout_button');
        // Hide title.
        add_filter('give_form_title', '__return_empty_string');
    }


    /**
     * @inheritDoc
     */
    public function loadScripts()
    {
        if ($this->scriptsLoaded) {
            return;
        }

        $this->scriptsLoaded = true;

        // Font
        $primaryFont = $this->options[ 'appearance' ][ 'primary_font' ];

        if (in_array($primaryFont, [ 'custom', 'montserrat' ])) {
            $font = ( 'montserrat' === $primaryFont )
                ? 'Montserrat'
                : $this->options[ 'appearance' ][ 'custom_font' ];

            wp_enqueue_style(
                'give-google-font',
                "https://fonts.googleapis.com/css?family={$font}:400,500,600,700&display=swap",
                [],
                GIVE_VERSION
            );
        }

        wp_enqueue_style(
            'give-classic-template',
            GIVE_PLUGIN_URL . 'assets/dist/css/give-classic-template.css',
            [],
            GIVE_VERSION
        );

        // We are replacing the Give styles with this template. Let’s not fight
        // against ourselves. This will help us not need to write such specific
        // styles so that users can still override ours.
        add_action('wp_enqueue_scripts', function () {
            wp_dequeue_style('give-styles');
            wp_dequeue_style('give_recurring_css');
        }, 10);

        // CSS Variables
        wp_add_inline_style(
            'give-classic-template',
            $this->loadFile('css/variables.php', [
                'primaryColor'          => $this->options[ 'appearance' ][ 'primary_color' ],
                'headerBackgroundImage' => $this->options[ 'appearance' ][ 'header_background_image' ],
            ])
        );

        // Inline CSS
        wp_add_inline_style(
            'give-classic-template',
            $this->loadFile('css/inline.css')
        );

        // JS
        wp_enqueue_script(
            'give-classic-template-js',
            GIVE_PLUGIN_URL . 'assets/dist/js/give-classic-template.js',
            [ 'give' ],
            GIVE_VERSION,
            true
        );

        wp_localize_script(
            'give-classic-template-js',
            'classicTemplateOptions',
            $this->options
        );
    }

    /**
     * @inheritDoc
     */
    public function getLoadingView()
    {
        return $this->loadFile('views/loading.php', [
            'options' => $this->options[ 'appearance' ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public function renderLoadingView($formId = null)
    {
        echo $this->getLoadingView();
    }

    /**
     * @inheritDoc
     */
    public function getReceiptView()
    {
        return wp_doing_ajax()
            ? $this->getFilePath('views/receipt.php')
            : parent::getReceiptView();
    }

    /**
     * Render donation form header
     */
    public function renderHeader()
    {
        echo $this->loadFile('views/header.php', [
            'title'                => $this->options[ 'appearance' ][ 'main_heading' ],
            'description'          => $this->options[ 'appearance' ][ 'description' ],
            'isSecureBadgeEnabled' => $this->options[ 'appearance' ][ 'secure_badge' ] === 'enabled',
            'secureBadgeContent'   => $this->options[ 'appearance' ][ 'secure_badge_text' ],
        ]);
    }

    /**
     * Render donation amount heading
     */
    public function renderDonationAmountHeading()
    {
        echo $this->loadFile('views/donation-amount-heading.php', [
            'content' => $this->options[ 'donation_amount' ][ 'headline' ],
        ]);
    }

    /**
     * Render the SVG icon definitions.
     *
     * @void
     */
    public function renderIconDefinitions()
    {
        echo $this->loadFile('views/icon-defs.php');
    }

    /**
     * @inheritDoc
     */
    public function getReceiptDetails($donationId)
    {
        $receipt = new DonationReceipt($donationId);

        $receipt->heading = esc_html(give_do_email_tags($this->options['donation_receipt']['headline'], [ 'payment_id' => $donationId ]));
        $receipt->message = wp_kses_post(give_do_email_tags($this->options['donation_receipt']['description'], [ 'payment_id' => $donationId ]));

        /**
         * Fire the action for receipt object.
         *
         * @since 2.7.0
         */
        do_action('give_new_receipt', $receipt);

        return $receipt;
    }

    /**
     * Load file
     *
     * @param  string  $file
     * @param  array  $args
     *
     * @return string
     * @throws InvalidArgumentException
     *
     */
    protected function loadFile($file, $args = [])
    {
        $filePath = $this->getFilePath($file);

        if (! file_exists($filePath)) {
            throw new InvalidArgumentException("File {$filePath} does not exist");
        }

        ob_start();
        extract($args);
        include $filePath;

        return ob_get_clean();
    }

    /**
     * Get file path
     *
     * @param  string  $file
     *
     * @return string
     */
    protected function getFilePath($file = '')
    {
        return GIVE_PLUGIN_DIR . "src/Views/Form/Templates/Classic/resources/{$file}";
    }
}
