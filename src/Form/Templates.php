<?php

/**
 * Handle Form Templates
 *
 * @package Give
 * @since   2.7.0
 */

namespace Give\Form;

use Give\Views\Form\Templates\Legacy\Legacy;
use Give\Views\Form\Templates\Sequoia\Sequoia;
use Give\Helpers\Form\Template as FormTemplateUtils;

defined( 'ABSPATH' ) || exit;

/**
 * Class Templates
 *
 * @package Give\Form
 *
 * @since   2.7.0
 */
class Templates {
	/**
	 * Templates
	 *
	 * @var array
	 */
	private $templates = [];


	/**
	 * Template Objects
	 *
	 * @var Template[]
	 */
	private $templateObjs = [];

	/**
	 * Load templates
	 *
	 * @since 2.7.0
	 */
	public function load() {
		/**
		 * Filter list of form template
		 *
		 * @param Template[]
		 *
		 * @since 2.7.0
		 */
		$this->templates = apply_filters(
			'give_register_form_template',
			[
				'sequoia' => Sequoia::class,
				'legacy'  => Legacy::class,
			]
		);
	}

	/**
	 * Get Registered templates
	 *
	 * @return Template[]
	 * @since 2.7.0
	 */
	public function getTemplates() {
		// Check if all templates have there object or not.
		$remainingObjs = array_diff( array_keys( $this->templates ), array_keys( $this->templateObjs ) );

		// Get object if any remaining
		if ( $remainingObjs ) {
			foreach ( $remainingObjs as $templateId ) {
				$this->templateObjs[ $templateId ] = $this->getTemplateObject( $templateId );
			}
		}

		return $this->templateObjs;
	}

	/**
	 * Get Registered form template
	 *
	 * @param string $templateId Template Id. Default to active form template.
	 *
	 * @return Template
	 * @since 2.7.0
	 */
	public function getTemplate( $templateId = null ) {
		$templateId = $templateId ?: FormTemplateUtils::getActiveID();

		if ( isset( $this->templateObjs[ $templateId ] ) ) {
			return $this->templateObjs[ $templateId ];
		}

		$this->templateObjs[ $templateId ] = $this->getTemplateObject( $templateId );

		return $this->getTemplateObject( $templateId );
	}

	/**
	 * Get class object.
	 *
	 * @param string $templateId
	 *
	 * @return Template
	 * @since 2.7.0
	 */
	private function getTemplateObject( $templateId ) {
		return new $this->templates[ $templateId ]();
	}
}
