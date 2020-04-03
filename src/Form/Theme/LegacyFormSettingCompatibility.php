<?php
namespace Give\Form\Theme;

/**
 * Class LegacyFormSettingCompatibility
 *
 * @since 2.7.0
 * @package Give\Form\Theme
 */
abstract class LegacyFormSettingCompatibility {
	/**
	 * @var array $defaultSettings Form settings default values for form template.
	 */
	private $defaultLegacySettingValues = [
		'_give_display_style'        => 'button',
		'_give_payment_display'      => 'onpage',
		'_give_form_floating_labels' => 'disabled',
		'_give_display_content'      => 'disabled',
	];

	/**
	 * Map form template setting to form setting.
	 *
	 * Note: either this array will be empty of contain field id as key and legacy setting key as value grouped by group id.
	 * For example:
	 * [
	 *    'introduction' => [
	 *        'content' => '_give_display_content'
	 *        ......
	 *    ],
	 *    .......
	 * ]
	 *
	 * @var array $mapToLegacySetting
	 */
	private $mapToLegacySetting = [];


	/**
	 * Save legacy settings.
	 *
	 * Note: This function must be called when saving donation form in WP Backed.
	 *
	 * @param array $settings
	 * @since 2.7.0
	 */
	public function saveLegacySettings( $settings ) {
		$alreadySavedLegacySettings = [];
		$formId                     = absint( $_GET['post_ID'] );

		if ( $this->mapToLegacySetting ) {
			foreach ( $this->mapToLegacySetting as $groupId => $group ) {
				foreach ( $group as $fieldId => $legacySettingMetaKey ) {
					// Continue if setting is not find.
					if ( ! isset( $settings[ $groupId ][ $fieldId ] ) ) {
						continue;
					}

					Give()->form_meta->update( $formId, $legacySettingMetaKey, $settings[ $groupId ][ $fieldId ] );
					$alreadySavedLegacySettings[] = $legacySettingMetaKey;
				}
			}
		}

		if ( $remainingSettings = array_diff( array_keys( $this->defaultLegacySettingValues ), $alreadySavedLegacySettings ) ) {
			foreach ( $remainingSettings as $metaKey => $metaValue ) {
				Give()->form_meta->update( $formId, $metaKey, $metaKey );
			}
		}
	}
}
