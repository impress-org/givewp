<?php
namespace Give\Form\Template;

use Give\Form\Template;

/**
 * Class LegacyFormSettingCompatibility
 *
 * @since 2.7.0
 * @package Give\Form\Template
 */
class LegacyFormSettingCompatibility {
	/**
	 * @var string
	 */
	public static $key = 'mapToLegacySetting';

	/**
	 * @var array $mapToTemplateProperty Form settings default values for form template.
	 */
	private $mapToTemplateProperty = [
		'_give_display_style'        => 'donationLevelsDisplayStyle',
		'_give_payment_display'      => 'donationFormDisplayStyle',
		'_give_form_floating_labels' => 'enableFloatLabels',
		'_give_reveal_label'         => 'continueToDonationFormLabel',
		'_give_checkout_label'       => 'donateNowButtonLabel',
		'_give_display_content'      => 'showDonationIntroductionContent',
		'_give_content_placement'    => 'donationIntroductionContentPosition',
		'_give_form_content'         => 'donationIntroductionContent',
	];

	/**
	 * @var Template $template
	 */
	private $template;

	/**
	 * LegacyFormSettingCompatibility constructor.
	 *
	 * @param Template $template
	 */
	public function __construct( Template $template ) {
		$this->template = $template;
	}

	/**
	 * Save legacy settings.
	 *
	 * Note: we are using this function internally to store legacy form settings when save form template setting.
	 *
	 * @see src/Helpers/Form/Theme/Theme.php:46  we are using this function in set function.
	 * @param int   $formId
	 * @param array $settings
	 * @since 2.7.0
	 */
	public function save( $formId, $settings ) {
		$alreadySavedLegacySettings = [];
		$templateOptions            = $this->template->getOptionsConfig();

		foreach ( $templateOptions as $groupId => $group ) {
			foreach ( $group['fields'] as $field ) {
				// Continue if setting is not map to legacy setting.
				if ( ! isset( $field[ self::$key ] ) ) {
					continue;
				}

				Give()->form_meta->update_meta( $formId, $field[ self::$key ], $settings[ $groupId ][ $field['id'] ] );
				$alreadySavedLegacySettings[] = $field[ self::$key ];
			}
		}

		if ( $remainingSettings = array_diff( array_keys( $this->mapToTemplateProperty ), $alreadySavedLegacySettings ) ) {
			foreach ( $remainingSettings as $metaKey ) {
				Give()->form_meta->update_meta(
					$formId,
					$metaKey,
					$this->template->{$this->mapToTemplateProperty[ $metaKey ]}
				);
			}
		}
	}
}
