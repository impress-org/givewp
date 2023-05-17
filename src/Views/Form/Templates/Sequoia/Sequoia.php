<?php

namespace Give\Views\Form\Templates\Sequoia;

use Give\Form\Template;
use Give\Form\Template\Hookable;
use Give\Form\Template\Scriptable;
use Give\Helpers\Form\Template as FormTemplateUtils;
use Give\Helpers\Utils;
use Give\Receipt\DonationReceipt;
use Give_Donate_Form as DonationForm;
use Give_Scripts;

use function give_do_email_tags as formatContent;
use function give_is_setting_enabled;

/**
 * Class Sequoia
 *
 * @package Give\Views\Form\Templates
 */
class Sequoia extends Template implements Hookable, Scriptable
{
    /**
     * Contains the options for the Sequoia form template.
     * @since 2.27.0
     * @var array
     */
    private $options;

    public function __construct()
    {
        $this->options = FormTemplateUtils::getOptions();
    }

    /**
     * @inheritDoc
     */
    public function getFormStartingHeight($formId)
    {
        $form = new DonationForm($formId);
        $templateOptions = FormTemplateUtils::getOptions($formId);
        if ($templateOptions['introduction']['enabled'] === 'disabled') {
            return 645;
        }
        $goalHeight = ! $form->has_goal() ? 0 : 123;
        $imageHeight = empty($templateOptions['introduction']['image']) && empty(
            get_post_thumbnail_id(
                $formId
            )
        ) ? 0 : 175;

        return 423 + $goalHeight + $imageHeight;
    }

    /**
     * @inheritDoc
     */
    public function getLoadingView()
    {
        return GIVE_PLUGIN_DIR . 'src/Views/Form/Templates/Sequoia/views/loading.php';
    }

    /**
     * @inheritDoc
     */
    public function getReceiptView()
    {
        return wp_doing_ajax(
        ) ? GIVE_PLUGIN_DIR . 'src/Views/Form/Templates/Sequoia/views/receipt.php' : parent::getReceiptView();
    }

    /**
     * @inheritDoc
     */
    public function loadHooks()
    {
        $actions = new Actions();
        $actions->init();
    }

    /**
     * @inheritDoc
     * @since 2.16.0 Load google fonts if "enabled".
     */
    public function loadScripts()
    {
        // Localize Template options
        $templateOptions = FormTemplateUtils::getOptions();

        // Set defaults
        $templateOptions['visual_appearance']['google-fonts'] = ! empty($templateOptions['visual_appearance']['google-fonts']) ? $templateOptions['visual_appearance']['google-fonts'] : 'enabled';
        $templateOptions['introduction']['donate_label'] = ! empty($templateOptions['introduction']['donate_label']) ? $templateOptions['introduction']['donate_label'] : __(
            'Donate Now',
            'give'
        );
        $templateOptions['visual_appearance']['primary_color'] = ! empty($templateOptions['visual_appearance']['primary_color']) ? $templateOptions['visual_appearance']['primary_color'] : '#28C77B';
        $templateOptions['payment_amount']['next_label'] = ! empty($templateOptions['payment_amount']['next_label']) ? $templateOptions['payment_amount']['next_label'] : __(
            'Continue',
            'give'
        );
        $templateOptions['payment_amount']['header_label'] = ! empty($templateOptions['payment_amount']['header_label']) ? $templateOptions['payment_amount']['header_label'] : __(
            'Choose Amount',
            'give'
        );
        $templateOptions['payment_information']['header_label'] = ! empty($templateOptions['payment_information']['header_label']) ? $templateOptions['payment_information']['header_label'] : __(
            'Add Your Information',
            'give'
        );
        $templateOptions['payment_information']['checkout_label'] = ! empty($templateOptions['payment_information']['checkout_label']) ? $templateOptions['payment_information']['checkout_label'] : __(
            'Process Donation',
            'give'
        );

        $isGoogleFontEnabled = give_is_setting_enabled($templateOptions['visual_appearance']['google-fonts']);

        if ($isGoogleFontEnabled) {
            wp_enqueue_style(
                'give-google-font-montserrat',
                'https://fonts.googleapis.com/css?family=Montserrat:400,500,600,700&display=swap',
                [],
                GIVE_VERSION
            );
        }

        // If default Give styles are disabled globally, enqueue Give default styles here
        if (! give_is_setting_enabled(give_get_option('css'))) {
            wp_enqueue_style('give-styles', (new Give_Scripts())->get_frontend_stylesheet_uri(), [], GIVE_VERSION, 'all');
        }

        // Enqueue Sequoia template styles
        wp_enqueue_style(
            'give-sequoia-template-css',
            GIVE_PLUGIN_URL . 'assets/dist/css/give-sequoia-template.css',
            ['give-styles'],
            GIVE_VERSION
        );

        $primaryColor = $templateOptions['visual_appearance']['primary_color'];
        $dynamicCss = sprintf(
            '
			.seperator {
				background: %1$s !important;
			}
			.give-btn {
				border: 2px solid %1$s !important;
				background: %1$s !important;
			}
			.give-btn:hover {
				background: %1$s !important;
			}
			.give-btn:focus {
				box-shadow: 0 0 8px %1$s;
			}
			.payment .give-gateway-option-selected:focus-within .give-gateway-option::before,
			.choose-amount .give-total-wrap .give-donation-amount:focus-within {
				border-color: %1$s !important;
			}
			.give-donation-level-btn {
				border: 2px solid %1$s !important;
			}
			.give-donation-level-btn.give-default-level {
				color: %1$s !important;
				background: #fff !important;
				transition: background 0.2s ease, color 0.2s ease;
			}
			.give-donation-level-btn.give-default-level:hover {
				color: %1$s !important; background: #fff !important;
			}
			.give-input:focus, .give-select:focus {
				border: 1px solid %1$s !important;
			}
			.checkmark {
				border-color: %1$s !important;
				color: %1$s !important;
			}
			input[type=\'radio\'] + label::after,
			[data-field-type=\'radio\'] label::after{
				background: %1$s !important;
			}
			input[type=\'radio\']:focus + label::before{
				border-color: %1$s;
			}
			a {
				color: %1$s;
			}
			.give-square-cc-fields:focus,
			.give-stripe-cc-field:focus,
			.give-stripe-single-cc-field-wrap:focus,
			form[id*="give-form"] .form-row textarea:focus,
			form[id*="give-form"] .form-row textarea.required:focus,
			form[id*="give-form"] .form-row input:focus,
			form[id*="give-form"] .form-row input.required:focus,
			#give-recurring-form .form-row textarea:focus,
			#give-recurring-form .form-row textarea.required:focus,
			#give-recurring-form .form-row input:focus,
			#give-recurring-form .form-row input.required:focus,
			form.give-form .form-row textarea:focus,
			form.give-form .form-row textarea.required:focus,
			form.give-form .form-row input:focus,
			form.give-form .form-row input.required:focus,
			.form-row select, #give-recurring-form .form-row select:focus,
			form.give-form .form-row select:focus,
			.form-row select.required:focus,
			#give-recurring-form .form-row select.required:focus,
			form.give-form .form-row select.required:focus,
			.give-select:focus,
			.give-input-field-wrapper.has-focus,
			[data-field-type="checkbox"] label:focus-within::before,
			[data-field-type="radio"] label:focus-within::before {
				border-color: %1$s !important;
			}
			',
            $primaryColor
        );

        $rawColor = trim($primaryColor, '#');
        $dynamicCss .= "
			.payment [id*='give-create-account-wrap-'] label::after {
				background-image: url(\"data:image/svg+xml,%3Csvg width='15' height='11' viewBox='0 0 15 11' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5.73047 10.7812C6.00391 11.0547 6.46875 11.0547 6.74219 10.7812L14.7812 2.74219C15.0547 2.46875 15.0547 2.00391 14.7812 1.73047L13.7969 0.746094C13.5234 0.472656 13.0859 0.472656 12.8125 0.746094L6.25 7.30859L3.16016 4.24609C2.88672 3.97266 2.44922 3.97266 2.17578 4.24609L1.19141 5.23047C0.917969 5.50391 0.917969 5.96875 1.19141 6.24219L5.73047 10.7812Z' fill='%23{$rawColor}'/%3E%3C/svg%3E%0A\");
			}
			#give_terms_agreement:hover,
			#give_terms_agreement:focus-within,
			#give_terms_agreement.active {
				border: 1px solid {$primaryColor} !important;
			}
			#give_terms_agreement input[type='checkbox']:focus + label::before {
				border-color: {$primaryColor};
			}
			#give_terms_agreement input[type='checkbox'] + label::after,
			#give-anonymous-donation-wrap label::after,
			[data-field-type='checkbox'] label.active:after {
				background-image: url(\"data:image/svg+xml,%3Csvg width='15' height='11' viewBox='0 0 15 11' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5.73047 10.7812C6.00391 11.0547 6.46875 11.0547 6.74219 10.7812L14.7812 2.74219C15.0547 2.46875 15.0547 2.00391 14.7812 1.73047L13.7969 0.746094C13.5234 0.472656 13.0859 0.472656 12.8125 0.746094L6.25 7.30859L3.16016 4.24609C2.88672 3.97266 2.44922 3.97266 2.17578 4.24609L1.19141 5.23047C0.917969 5.50391 0.917969 5.96875 1.19141 6.24219L5.73047 10.7812Z' fill='%23{$rawColor}'/%3E%3C/svg%3E%0A\") !important;
			}
			#give-anonymous-donation-wrap label:focus-within::before {
				border-color: {$primaryColor} !important;
			}
		";

        if (Utils::isPluginActive('give-recurring/give-recurring.php')) {
            $dynamicCss .= "
				.give-recurring-donors-choice:hover,
				.give-recurring-donors-choice:focus-within,
				.give-recurring-donors-choice.active {
					border: 1px solid {$primaryColor};
				}
				.give-recurring-donors-choice .give-recurring-donors-choice-period:focus,
				.give-recurring-donors-choice input[type='checkbox']:focus + label::before {
					border-color: {$primaryColor};
				}
				.give-recurring-donors-choice input[type='checkbox'] + label::after {
					background-image: url(\"data:image/svg+xml,%3Csvg width='15' height='11' viewBox='0 0 15 11' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5.73047 10.7812C6.00391 11.0547 6.46875 11.0547 6.74219 10.7812L14.7812 2.74219C15.0547 2.46875 15.0547 2.00391 14.7812 1.73047L13.7969 0.746094C13.5234 0.472656 13.0859 0.472656 12.8125 0.746094L6.25 7.30859L3.16016 4.24609C2.88672 3.97266 2.44922 3.97266 2.17578 4.24609L1.19141 5.23047C0.917969 5.50391 0.917969 5.96875 1.19141 6.24219L5.73047 10.7812Z' fill='%23{$rawColor}'/%3E%3C/svg%3E%0A\");
				}
			";
        }

        if (Utils::isPluginActive('give-fee-recovery/give-fee-recovery.php')) {
            $dynamicCss .= "
				.give-fee-recovery-donors-choice.give-fee-message:hover,
				.give-fee-recovery-donors-choice.give-fee-message:focus-within,
				.give-fee-recovery-donors-choice.give-fee-message.active {
					border: 1px solid {$primaryColor};
				}
				.give-fee-message-label input[type='checkbox']:focus + span::before {
					border-color: {$primaryColor};
				}
				.give-fee-recovery-donors-choice.give-fee-message input[type='checkbox'] + .give-fee-message-label-text::after {
					background-image: url(\"data:image/svg+xml,%3Csvg width='15' height='11' viewBox='0 0 15 11' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5.73047 10.7812C6.00391 11.0547 6.46875 11.0547 6.74219 10.7812L14.7812 2.74219C15.0547 2.46875 15.0547 2.00391 14.7812 1.73047L13.7969 0.746094C13.5234 0.472656 13.0859 0.472656 12.8125 0.746094L6.25 7.30859L3.16016 4.24609C2.88672 3.97266 2.44922 3.97266 2.17578 4.24609L1.19141 5.23047C0.917969 5.50391 0.917969 5.96875 1.19141 6.24219L5.73047 10.7812Z' fill='%23{$rawColor}'/%3E%3C/svg%3E%0A\");
				}
			";
        }

        if (Utils::isPluginActive('give-activecampaign/give-activecampaign.php')) {
            $dynamicCss .= "
				.give-activecampaign-fieldset:hover,
				.give-activecampaign-fieldset:focus-within,
				.give-activecampaign-fieldset.active {
					border: 1px solid {$primaryColor} !important;
				}
				.give-activecampaign-fieldset input[type='checkbox']:focus + span::before {
					border-color: {$primaryColor};
				}
				.give-activecampaign-fieldset input[type='checkbox'] + span::after {
					background-image: url(\"data:image/svg+xml,%3Csvg width='15' height='11' viewBox='0 0 15 11' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5.73047 10.7812C6.00391 11.0547 6.46875 11.0547 6.74219 10.7812L14.7812 2.74219C15.0547 2.46875 15.0547 2.00391 14.7812 1.73047L13.7969 0.746094C13.5234 0.472656 13.0859 0.472656 12.8125 0.746094L6.25 7.30859L3.16016 4.24609C2.88672 3.97266 2.44922 3.97266 2.17578 4.24609L1.19141 5.23047C0.917969 5.50391 0.917969 5.96875 1.19141 6.24219L5.73047 10.7812Z' fill='%23{$rawColor}'/%3E%3C/svg%3E%0A\") !important;
				}
			";
        }

        if (Utils::isPluginActive('give-mailchimp/give-mailchimp.php')) {
            $dynamicCss .= "
				.give-mailchimp-fieldset:hover,
				.give-mailchimp-fieldset:focus-within,
				.give-mailchimp-fieldset.active {
					border: 1px solid {$primaryColor} !important;
				}
				.give-mailchimp-fieldset input[type='checkbox']:focus + span::before {
					border-color: {$primaryColor};
				}
				.give-mailchimp-fieldset input[type='checkbox'] + span::after {
					background-image: url(\"data:image/svg+xml,%3Csvg width='15' height='11' viewBox='0 0 15 11' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5.73047 10.7812C6.00391 11.0547 6.46875 11.0547 6.74219 10.7812L14.7812 2.74219C15.0547 2.46875 15.0547 2.00391 14.7812 1.73047L13.7969 0.746094C13.5234 0.472656 13.0859 0.472656 12.8125 0.746094L6.25 7.30859L3.16016 4.24609C2.88672 3.97266 2.44922 3.97266 2.17578 4.24609L1.19141 5.23047C0.917969 5.50391 0.917969 5.96875 1.19141 6.24219L5.73047 10.7812Z' fill='%23{$rawColor}'/%3E%3C/svg%3E%0A\") !important;
				}
			";
        }

        if (Utils::isPluginActive('give-constant-contact/give-constant-contact.php')) {
            $dynamicCss .= "
				.give-constant-contact-fieldset:hover,
				.give-constant-contact-fieldset:focus-within,
				.give-constant-contact-fieldset.active {
					border: 1px solid {$primaryColor} !important;
				}
				.give-constant-contact-fieldset input[type='checkbox']:focus + span::before {
					border-color: {$primaryColor};
				}
				.give-constant-contact-fieldset input[type='checkbox'] + span::after {
					background-image: url(\"data:image/svg+xml,%3Csvg width='15' height='11' viewBox='0 0 15 11' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5.73047 10.7812C6.00391 11.0547 6.46875 11.0547 6.74219 10.7812L14.7812 2.74219C15.0547 2.46875 15.0547 2.00391 14.7812 1.73047L13.7969 0.746094C13.5234 0.472656 13.0859 0.472656 12.8125 0.746094L6.25 7.30859L3.16016 4.24609C2.88672 3.97266 2.44922 3.97266 2.17578 4.24609L1.19141 5.23047C0.917969 5.50391 0.917969 5.96875 1.19141 6.24219L5.73047 10.7812Z' fill='%23{$rawColor}'/%3E%3C/svg%3E%0A\") !important;
				}
			";
        }

        if (Utils::isPluginActive('give-form-field-manager/give-ffm.php')) {
            $dynamicCss .= "
				.ffm-checkbox-field label.checked::after {
					background-image: url(\"data:image/svg+xml,%3Csvg width='15' height='11' viewBox='0 0 15 11' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5.73047 10.7812C6.00391 11.0547 6.46875 11.0547 6.74219 10.7812L14.7812 2.74219C15.0547 2.46875 15.0547 2.00391 14.7812 1.73047L13.7969 0.746094C13.5234 0.472656 13.0859 0.472656 12.8125 0.746094L6.25 7.30859L3.16016 4.24609C2.88672 3.97266 2.44922 3.97266 2.17578 4.24609L1.19141 5.23047C0.917969 5.50391 0.917969 5.96875 1.19141 6.24219L5.73047 10.7812Z' fill='%23{$rawColor}'/%3E%3C/svg%3E%0A\");
				}
				.ffm-radio-field label::after {
					background: {$primaryColor};
				}
				.ffm-attachment-upload-filelist:focus-within,
				.ffm-checkbox-field label:focus-within::before,
				.ffm-radio-field label:focus-within::before {
					border-color: {$primaryColor};
				}
			";
        }

        if (Utils::isPluginActive('give-tributes/give-tributes.php')) {
            $dynamicCss .= "
				.give-tributes-type-button-list input[type='radio']:checked + label.give-tribute-type-button {
				    color: {$primaryColor} !important;
				}
			";
        }

        if ($isGoogleFontEnabled) {
            $dynamicCss .= "body, button, input, select{font-family: 'Montserrat', sans-serif;}";
        }

        wp_add_inline_style('give-sequoia-template-css', $dynamicCss);

        wp_enqueue_script(
            'give-sequoia-template-js',
            GIVE_PLUGIN_URL . 'assets/dist/js/give-sequoia-template.js',
            ['give'],
            GIVE_VERSION,
            true
        );
        wp_localize_script('give-sequoia-template-js', 'sequoiaTemplateOptions', $templateOptions);
        wp_localize_script(
            'give-sequoia-template-js',
            'sequoiaTemplateL10n',
            [
                'optionalLabel' => sprintf('&nbsp;(%s)', esc_html__('optional', 'give')),
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getID()
    {
        return 'sequoia';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return __('Multi-Step Form', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getImage()
    {
        return GIVE_PLUGIN_URL . 'assets/dist/images/admin/template-preview-multi-step.png';
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
     * @since 2.15.0 Allow HTML in thank you message.
     */
    public function getReceiptDetails($donationId)
    {
        $receipt = new DonationReceipt($donationId);
        $options = FormTemplateUtils::getOptions();

        $receipt->heading = esc_html($options['thank-you']['headline']);
        $receipt->message = wp_kses_post(
            formatContent($options['thank-you']['description'], ['payment_id' => $donationId])
        );

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
        $templateOptions = FormTemplateUtils::getOptions($formId);

        return ! empty($templateOptions['introduction']['headline']) ?
            $templateOptions['introduction']['headline'] :
            get_the_title($formId);
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
        $templateOptions = FormTemplateUtils::getOptions($formId);

        return ! empty($templateOptions['introduction']['image']) ?
            $templateOptions['introduction']['image'] :
            get_the_post_thumbnail_url($formId, 'full');
    }

    /**
     * Get form excerpt
     *
     * @since 2.27.0 Display only form description in Multi-Step donation form template.
     * @since 2.19.6 Form excerpt has precedence over form description
     * @since 2.7.0
     *
     * @param int|null $formId
     *
     * @return string|void
     */
    public function getFormExcerpt($formId)
    {
        return $this->options['introduction']['description'];
    }
}
