<?php

namespace Give\Views\Form\Templates\Classic;

use Give\Helpers\Form\Template\Utils\Frontend;
use Give_Donate_Form;
use Give\Form\Template;
use Give\Form\Template\Hookable;
use Give\Form\Template\Scriptable;
use Give\Helpers\Form\Template as FormTemplateUtils;
use Give\Receipt\DonationReceipt;
use InvalidArgumentException;

/**
 * Classic Donation Form Template
 *
 * @since 2.18.0
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
        return __('Classic Form', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getImage()
    {
        return GIVE_PLUGIN_URL . 'assets/dist/images/admin/template-preview-classic.png';
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
        add_action('give_pre_form', [$this, 'renderIconDefinitions']);

        // Display header
        if ('enabled' === $this->options[ 'visual_appearance' ][ 'display_header' ]) {
            add_action('give_pre_form', [$this, 'renderHeader'], 10, 3);
        }

        add_action('give_before_donation_levels', [$this, 'renderDonationAmountHeading'], 20);

        $sections = [
            [
                'hooks' => ['give_donation_form_top'],
                'class' => 'give-donation-amount-section',
            ],
            [
                'hooks' => ['give_donation_form_register_login_fields'],
                'class' => 'give-personal-info-section',
            ],
            [
                'hooks' => ['give_payment_mode_top', 'give_payment_mode_bottom'],
                'class' => 'give-payment-details-section',
            ],
            [
                'hooks' => ['give_donation_form_before_submit', 'give_donation_form_after_submit'],
                'class' => 'give-donate-now-button-section',
            ],
            [
                'hooks' => ['give_donation_summary_top', 'give_donation_summary_bottom'],
                'class' => 'give-donation-form-summary-section',
            ],
        ];

        foreach ($sections as $section) {
            list ($start, $end) = array_pad($section[ 'hooks' ], 2, null);

            add_action($start, function () use ($section) {
                printf('<section class="give-form-section %s">', $section[ 'class' ]);
            }, -10000);

            add_action($end ? : $start, function () {
                echo '</section>';
            }, 10000);
        }

        /**
         * Remove actions
         */
        // Remove goal.
        remove_action('give_pre_form', 'give_show_goal_progress');
        // Remove intermediate continue button which appears when display style set to  anything other than "onpage".
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
        if ($primaryFont = $this->getPrimaryFont()) {
            wp_enqueue_style(
                'give-google-font',
                'https://fonts.googleapis.com/css?family=' . urlencode($primaryFont) . ':400,500,600,700&display=swap',
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
            wp_dequeue_style('give-currency-switcher-style');
            wp_dequeue_style('give-fee-recovery');
            wp_dequeue_style('give-donation-summary-style-frontend');
            wp_dequeue_style('give-authorize-css');
        }, 10);

        // CSS Variables
        wp_add_inline_style(
            'give-classic-template',
            $this->loadFile('css/variables.php', [
                'primaryColor'          => $this->options[ 'visual_appearance' ][ 'primary_color' ],
                'headerBackgroundImage' => $this->options[ 'visual_appearance' ][ 'header_background_image' ],
                'headerBackgroundColor' => $this->options[ 'visual_appearance' ][ 'header_background_color' ],
                'statsProgressBarColor' => give_get_meta(Frontend::getFormId(), '_give_goal_color', true),
                'primaryFont'           => $primaryFont ? : 'inherit'
            ])
        );

        // JS
        wp_enqueue_script(
            'give-classic-template-js',
            GIVE_PLUGIN_URL . 'assets/dist/js/give-classic-template.js',
            ['give'],
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
            'options' => $this->options[ 'visual_appearance' ]
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
     *
     * @since 2.19.0 use trinary operator instead of Coalesce operator to make code php 5.6 compatible.
     *
     * @param  int  $formId
     * @param  array  $args
     * @param  Give_Donate_Form  $form
     */
    public function renderHeader($formId, $args, $form)
    {
        $hasGoal = $form->has_goal();

        echo $this->loadFile('views/header.php', [
            'title'                => isset($this->options['visual_appearance']['main_heading']) ? $this->options['visual_appearance']['main_heading'] : $form->post_title,
            'description'          => $this->options[ 'visual_appearance' ][ 'description' ],
            'isSecureBadgeEnabled' => $this->options[ 'visual_appearance' ][ 'secure_badge' ] === 'enabled',
            'secureBadgeContent'   => $this->options[ 'visual_appearance' ][ 'secure_badge_text' ],
            'hasGoal'              => $hasGoal,
            'goalStats'            => $hasGoal ? $this->getFormGoalStats($form) : []
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

        $receipt->heading = esc_html(give_do_email_tags($this->options[ 'donation_receipt' ][ 'headline' ], ['payment_id' => $donationId]));
        $receipt->message = wp_kses_post(give_do_email_tags($this->options[ 'donation_receipt' ][ 'description' ], ['payment_id' => $donationId]));

        /**
         * Fire the action for receipt object.
         *
         * @since 2.7.0
         */
        do_action('give_new_receipt', $receipt);

        return $receipt;
    }

    public function getFormGoalStats(Give_Donate_Form $form)
    {
        $goalStats = give_goal_progress_stats($form->get_ID());
        $raisedRaw = $form->get_earnings();

        // Setup default raised value
        $raised = give_currency_filter(
            give_format_amount(
                $raisedRaw,
                [
                    'sanitize' => false,
                    'decimal'  => false,
                ]
            )
        );

        // Setup default count value
        $count = $form->get_sales();

        // Setup default count label
        $countLabel = _n('donation', 'donations', $count, 'give');

        // Setup default goal value
        $goal = give_currency_filter(
            give_format_amount(
                $form->get_goal(),
                [
                    'sanitize' => false,
                    'decimal'  => false,
                ]
            )
        );

        $stats = [
            'raised'     => $raised,
            'raisedRaw'  => $raisedRaw,
            'progress'   => $goalStats[ 'progress' ],
            'count'      => $count,
            'countLabel' => $countLabel,
            'goal'       => $goal,
            'goalRaw'    => $goalStats[ 'raw_goal' ],
        ];

        switch ($goalStats[ 'format' ]) {
            case 'donation':
                return array_merge($stats, [
                    'count' => $goalStats[ 'actual' ],
                    'goal'  => $goalStats[ 'goal' ],
                ]);

            case 'donors':
                return array_merge($stats, [
                    'count'      => $goalStats[ 'actual' ],
                    'countLabel' => _n('donor', 'donors', $count, 'give'),
                    'goal'       => $goalStats[ 'goal' ],
                ]);

            default:
                return $stats;
        }
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

    /**
     * @return string|null
     */
    protected function getPrimaryFont()
    {
        $primaryFont = $this->options[ 'visual_appearance' ][ 'primary_font' ];

        if ($primaryFont !== 'system') {
            return $primaryFont;
        }

        return null;
    }

    /**
     * @since 2.27.0 Return description from form settings.
     * @param int $formId
     *
     * @return string
     */
    public function getFormExcerpt($formId)
    {
        return $this->options['visual_appearance']['description'];
    }
}
