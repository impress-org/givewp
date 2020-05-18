<?php
namespace Give\Views\Form\Templates\Sequoia;

use Give\Form\Template;
use Give\Form\Template\Hookable;
use Give\Form\Template\Scriptable;
use Give\Receipt\DonationReceipt\DonationReceipt;
use Give\Helpers\Form\Template as FormTemplateUtils;
use \Give_Donate_Form as DonationForm;
use function give_do_email_tags as formatContent;


/**
 * Class Sequoia
 *
 * @package Give\Views\Form\Templates
 */
class Sequoia extends Template implements Hookable, Scriptable {
	/**
	 * @inheritDoc
	 */
	public function getFormStartingHeight( $formId ) {
		$form            = new DonationForm( $formId );
		$templateOptions = FormTemplateUtils::getOptions( $formId );
		if ( $templateOptions['introduction']['enabled'] === 'disabled' ) {
			return 645;
		}
		$goalHeight  = ! $form->has_goal() ? 0 : 123;
		$imageHeight = empty( $templateOptions['introduction']['image'] ) && empty( get_post_thumbnail_id( $formId ) ) ? 0 : 175;
		return 423 + $goalHeight + $imageHeight;
	}

	/**
	 * @inheritDoc
	 */
	public function getLoadingView() {
		return GIVE_PLUGIN_DIR . 'src/Views/Form/Templates/Sequoia/views/loading.php';
	}

	/**
	 * @inheritDoc
	 */
	public function getReceiptView() {
		return wp_doing_ajax() ? GIVE_PLUGIN_DIR . 'src/Views/Form/Templates/Sequoia/views/receipt.php' : parent::getReceiptView();
	}

	/**
	 * @inheritDoc
	 */
	public function loadHooks() {
		$actions = new Actions();
		$actions->init();
	}

	/**
	 * @inheritDoc
	 */
	public function loadScripts() {

		// Localize Template options
		$templateOptions = FormTemplateUtils::getOptions();

		// Set defaults
		$templateOptions['introduction']['donate_label']          = ! empty( $templateOptions['introduction']['donate_label'] ) ? $templateOptions['introduction']['donate_label'] : __( 'Donate Now', 'give' );
		$templateOptions['introduction']['primary_color']         = ! empty( $templateOptions['introduction']['primary_color'] ) ? $templateOptions['introduction']['primary_color'] : '#28C77B';
		$templateOptions['payment_amount']['next_label']          = ! empty( $templateOptions['payment_amount']['next_label'] ) ? $templateOptions['payment_amount']['next_label'] : __( 'Continue', 'give' );
		$templateOptions['payment_amount']['header_label']        = ! empty( $templateOptions['payment_amount']['header_label'] ) ? $templateOptions['payment_amount']['header_label'] : __( 'Choose Amount', 'give' );
		$templateOptions['payment_information']['header_label']   = ! empty( $templateOptions['payment_information']['header_label'] ) ? $templateOptions['payment_information']['header_label'] : __( 'Add Your Information', 'give' );
		$templateOptions['payment_information']['checkout_label'] = ! empty( $templateOptions['payment_information']['checkout_label'] ) ? $templateOptions['payment_information']['checkout_label'] : __( 'Process Donation', 'give' );

		wp_enqueue_style( 'give-google-font-montserrat', 'https://fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&display=swap', array(), GIVE_VERSION );
		wp_enqueue_style( 'give-sequoia-template-css', GIVE_PLUGIN_URL . 'assets/dist/css/give-sequoia-template.css', array( 'give-styles' ), GIVE_VERSION );

		$primaryColor = $templateOptions['introduction']['primary_color'];
		$dynamicCss   = sprintf(
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
			input[type=\'radio\'] + label::after {
				background: %1$s !important;
			}
			a.give-checkout-login {
				color: %1$s;
			}

		',
			$primaryColor
		);
		wp_add_inline_style( 'give-sequoia-template-css', $dynamicCss );

		$rawColor        = trim( $primaryColor, '#' );
		$registrationCss = "
			.payment [id*='give-create-account-wrap-'] label::after {
				background-image: url(\"data:image/svg+xml,%3Csvg width='15' height='11' viewBox='0 0 15 11' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5.73047 10.7812C6.00391 11.0547 6.46875 11.0547 6.74219 10.7812L14.7812 2.74219C15.0547 2.46875 15.0547 2.00391 14.7812 1.73047L13.7969 0.746094C13.5234 0.472656 13.0859 0.472656 12.8125 0.746094L6.25 7.30859L3.16016 4.24609C2.88672 3.97266 2.44922 3.97266 2.17578 4.24609L1.19141 5.23047C0.917969 5.50391 0.917969 5.96875 1.19141 6.24219L5.73047 10.7812Z' fill='%23{$rawColor}'/%3E%3C/svg%3E%0A\");
			}
		";
		wp_add_inline_style( 'give-sequoia-template-css', $registrationCss );

		$recurringDynamicCss = "
			.give-recurring-donors-choice:hover,
			.give-recurring-donors-choice.active {
				border: 1px solid {$primaryColor};
			}
			.give-recurring-donors-choice input[type='checkbox'] + label::after {
				background-image: url(\"data:image/svg+xml,%3Csvg width='15' height='11' viewBox='0 0 15 11' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5.73047 10.7812C6.00391 11.0547 6.46875 11.0547 6.74219 10.7812L14.7812 2.74219C15.0547 2.46875 15.0547 2.00391 14.7812 1.73047L13.7969 0.746094C13.5234 0.472656 13.0859 0.472656 12.8125 0.746094L6.25 7.30859L3.16016 4.24609C2.88672 3.97266 2.44922 3.97266 2.17578 4.24609L1.19141 5.23047C0.917969 5.50391 0.917969 5.96875 1.19141 6.24219L5.73047 10.7812Z' fill='%23{$rawColor}'/%3E%3C/svg%3E%0A\");
			}
		";
		wp_add_inline_style( 'give-sequoia-template-css', $recurringDynamicCss );

		$feeRecoveryDynamicCss = "
			.give-fee-recovery-donors-choice.give-fee-message:hover,
			.give-fee-recovery-donors-choice.give-fee-message.active {
				border: 1px solid {$primaryColor};
			}
			.give-fee-recovery-donors-choice.give-fee-message input[type='checkbox'] + .give-fee-message-label-text::after {
				background-image: url(\"data:image/svg+xml,%3Csvg width='15' height='11' viewBox='0 0 15 11' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5.73047 10.7812C6.00391 11.0547 6.46875 11.0547 6.74219 10.7812L14.7812 2.74219C15.0547 2.46875 15.0547 2.00391 14.7812 1.73047L13.7969 0.746094C13.5234 0.472656 13.0859 0.472656 12.8125 0.746094L6.25 7.30859L3.16016 4.24609C2.88672 3.97266 2.44922 3.97266 2.17578 4.24609L1.19141 5.23047C0.917969 5.50391 0.917969 5.96875 1.19141 6.24219L5.73047 10.7812Z' fill='%23{$rawColor}'/%3E%3C/svg%3E%0A\");
			}
		";
		wp_add_inline_style( 'give-sequoia-template-css', $feeRecoveryDynamicCss );

		$ffmDynamicCss = "
			.ffm-checkbox-field label.checked::after {
				background-image: url(\"data:image/svg+xml,%3Csvg width='15' height='11' viewBox='0 0 15 11' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5.73047 10.7812C6.00391 11.0547 6.46875 11.0547 6.74219 10.7812L14.7812 2.74219C15.0547 2.46875 15.0547 2.00391 14.7812 1.73047L13.7969 0.746094C13.5234 0.472656 13.0859 0.472656 12.8125 0.746094L6.25 7.30859L3.16016 4.24609C2.88672 3.97266 2.44922 3.97266 2.17578 4.24609L1.19141 5.23047C0.917969 5.50391 0.917969 5.96875 1.19141 6.24219L5.73047 10.7812Z' fill='%23{$rawColor}'/%3E%3C/svg%3E%0A\");
			}
			.ffm-radio-field label::after {
				background: {$primaryColor};
			}
		";
		wp_add_inline_style( 'give-sequoia-template-css', $ffmDynamicCss );

		wp_enqueue_script( 'give-sequoia-template-js', GIVE_PLUGIN_URL . 'assets/dist/js/give-sequoia-template.js', array( 'give' ), GIVE_VERSION, true );
		wp_localize_script( 'give-sequoia-template-js', 'sequoiaTemplateOptions', $templateOptions );
	}

	/**
	 * @inheritDoc
	 */
	public function getID() {
		return 'sequoia';
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return __( 'Sequoia - Multi-Step Form', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getImage() {
		return 'https://images.unsplash.com/photo-1448387473223-5c37445527e7?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=300&q=100';
	}

	/**
	 * @inheritDoc
	 */
	public function getOptionsConfig() {
		return require 'optionConfig.php';
	}

	/**
	 * @inheritDoc
	 */
	public function getReceiptDetails( $donationId ) {
		$receipt = new DonationReceipt( $donationId );
		$options = FormTemplateUtils::getOptions();

		$receipt->heading = esc_html( $options['thank-you']['headline'] );
		$receipt->message = esc_html( formatContent( $options['thank-you']['description'], array( 'payment_id' => $donationId ) ) );

		/**
		 * fire the action for receipt object.
		 *
		 * @since 2.7.0
		 */
		 do_action( 'give_new_receipt', $receipt );

		return $receipt;
	}
}
