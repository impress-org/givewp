<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\Helpers\Hooks;
use Give\PaymentGateways\PaymentGateway;

/**
 * Class PayPalCommerce
 *
 * Boots the PayPalCommerce gateway and provides its basic registration properties
 *
 * @since 2.8.0
 */
class PayPalCommerce implements PaymentGateway {
	/**
	 * @inheritDoc
	 */
	public function getId() {
		return 'paypal-commerce';
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return esc_html__( 'PayPal Donations', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getPaymentMethodLabel() {
		return esc_html__( 'Credit Card', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getOptions() {
		return [
			[
				'type'       => 'title',
				'id'         => 'give_title_gateway_settings_2',
				'table_html' => false,
			],
			[
				'name' => esc_html__( 'Connect With Paypal', 'give' ),
				'id'   => 'paypal_commerce_account_manger',
				'type' => 'paypal_commerce_account_manger',
			],
			[
				'type'       => 'sectionend',
				'id'         => 'give_title_gateway_settings_2',
				'table_html' => false,
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		Hooks::addAction( 'wp_ajax_give_paypal_commerce_user_on_boarded', AjaxRequestHandler::class, 'onBoardedUserAjaxRequestHandler' );
		Hooks::addAction( 'wp_ajax_give_paypal_commerce_get_partner_url', AjaxRequestHandler::class, 'onGetPartnerUrlAjaxRequestHandler' );
		Hooks::addAction( 'wp_ajax_give_paypal_commerce_disconnect_account', AjaxRequestHandler::class, 'removePayPalAccount' );
		Hooks::addAction( 'wp_ajax_give_paypal_commerce_create_order', AjaxRequestHandler::class, 'createOrder' );
		Hooks::addAction( 'wp_ajax_nopriv_give_paypal_commerce_create_order', AjaxRequestHandler::class, 'createOrder' );
		Hooks::addAction( 'wp_ajax_give_paypal_commerce_approve_order', AjaxRequestHandler::class, 'approveOrder' );
		Hooks::addAction( 'wp_ajax_nopriv_give_paypal_commerce_approve_order', AjaxRequestHandler::class, 'approveOrder' );

		Hooks::addAction( 'admin_enqueue_scripts', ScriptLoader::class, 'loadAdminScripts' );
		Hooks::addAction( 'wp_enqueue_scripts', ScriptLoader::class, 'loadPublicAssets' );

		Hooks::addAction( 'give_paypal_commerce_refresh_token', RefreshToken::class, 'refreshToken' );
		Hooks::addAction( 'give_paypal-commerce_cc_form', AdvancedCardFields::class, 'addCreditCardForm', 10, 3 );
		Hooks::addAction( 'give_gateway_paypal-commerce', DonationProcessor::class, 'handle' );
	}
}
