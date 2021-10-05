<?php

namespace Give\TestData\Repositories;

use Give\TestData\Factories\DonationFormFactory;
use Give\TestData\Framework\MetaRepository;


class DonationFormRepository {

	/**
	 * @var DonationFormFactory
	 */
	private $donationFormFactory;

	public function __construct( DonationFormFactory $donationFormFactory ) {
		$this->donationFormFactory = $donationFormFactory;
	}

	/**
	 * @param array $form
	 */
	public function insertDonationForm( $form ) {
		global $wpdb;

		$form = wp_parse_args(
			apply_filters( 'give-test-data-form-definition', $form ),
			$this->donationFormFactory->definition()
		);

		// Insert donation
		$wpdb->insert(
			"{$wpdb->prefix}posts",
			[
				'post_type'   => 'give_forms',
				'post_title'  => $form['post_title'],
				'post_name'   => $form['post_name'],
				'post_date'   => $form['post_date'],
				'post_author' => $form['post_author'],
				'post_status' => 'publish',
			]
		);

		$formId = $wpdb->insert_id;

		$metaRepository = new MetaRepository( 'give_formmeta', 'form_id' );

		$formMeta = [
			'_give_form_status'           => 'open',
			'_give_form_template'         => $form['form_template'],
			'_give_levels_minimum_amount' => '10.000000',
			'_give_levels_maximum_amount' => '250.000000',
			'_give_set_price'             => $form['random_amount'],
			"_give_{$form['form_template']}_form_template_settings" => serialize(
				[
					'introduction'        => [
						'enabled'       => 'enabled',
						'headline'      => $form['post_title'],
						'description'   => 'Help our organization by donating today! All donations go directly to making a difference for our cause.',
						'image'         => '',
						'primary_color' => '#28C77B',
						'donate_label'  => 'Donate Now',
					],
					'payment_amount'      => [
						'header_label' => 'Choose Amount',
						'content'      => '',
						'next_label'   => 'Continue',
					],
					'payment_information' => [
						'header_label'   => 'Add Your Information',
						'headline'       => "Who's giving today?",
						'description'    => 'We’ll never share this information with anyone.',
						'checkout_label' => 'Donate Now',
					],
					'thank-you'           => [
						'image'               => '',
						'headline'            => 'A great big thank you!',
						'description'         => '{name}, your contribution means a lot and will be put to good use in making a difference. We’ve sent your donation receipt to {donor_email}.',
						'sharing'             => 'enabled',
						'sharing_instruction' => 'Help spread the word by sharing your support with your friends and followers!',
						'twitter_message'     => "I just gave to this cause . Who's next?",
					],
				]
			),
		];

		// Generate terms and conditions
		if ( ! empty( $form['donation_terms'] ) ) {
			$formMeta['_give_terms_option'] = 'enabled';
			$formMeta['_give_agree_label']  = $form['donation_terms']['label'];
			$formMeta['_give_agree_text']   = $form['donation_terms']['text'];
		}

		// Set donation goal
		if ( $form['donation_goal'] ) {
			$formMeta['_give_goal_option'] = 'enabled';
			$formMeta['_give_goal_format'] = 'amount';
			$formMeta['_give_set_goal']    = $form['donation_goal'];
		}

		// Insert meta
		$metaRepository->persist( $formId, $formMeta );

		do_action( 'give-test-data-insert-donation-form', $formId, $form );
	}
}
