<?php

namespace Give\Form\Migrations;

use Give\Framework\Migrations\Contracts\Migration;
use Give\Helpers\Form\Template;
use Give\Views\Form\Templates\Sequoia\Sequoia;
use Give_Donate_Form;
use Give_Forms_Query;
use Give_Updates;

/**
 * @unreleased
 */
class MoveOptionsToVisualAppearanceSection extends Migration {
	/**
	 * @unreleased
	 */
	public function register() {
		Give_Updates::get_instance()->register(
			[
				'id'       => self::id(),
				'version'  => '2.15.1',
				'callback' => [ $this, 'run' ],
			]
		);
	}

	/**
	 * @unreleased
	 * @return string
	 */
	public static function id() {
		return 'move-options-to-visual-appearance-section';
	}

	/**
	 * @unreleased
	 */
	public function run() {
		$perPage = 20;

		// Get donation forms
		/* @var Give_Donate_Form[] $forms */
		$forms = ( new Give_Forms_Query( [
			'status'         => 'publish',
			'offset'         => ( Give_Updates::get_instance()->step - 1 ) * $perPage,
			'posts_per_page' => $perPage,
		] ) )->get_forms();

		if ( $forms ) {
			Give_Updates::get_instance()->set_percentage(
				$this->getDonationFormCount(),
				Give_Updates::get_instance()->step * $perPage
			);

			foreach ( $forms as $form ) {
				if ( give( Sequoia::class )->getID() === Template::getActiveID( $form->ID ) ) {
					Template::saveOptions(
						$form->ID,
						Template::getOptions( $form->ID )
					);
				}
			}

			return;
		}

		give_set_upgrade_complete( self::id() );
	}

	/**
	 * @unreleased
	 * @return false|int
	 */
	public static function timestamp() {
		return strtotime( '2021-10-20' );
	}

	/**
	 * @unreleased
	 *
	 * @return int
	 */
	private function getDonationFormCount() {
		// Get donation forms
		$result = ( new Give_Forms_Query( [
			'status' => 'publish',
			'number' => - 1,
			'output' => ''
		] ) )->get_forms();

		return $result ? count( $result ) : 0;
	}
}
