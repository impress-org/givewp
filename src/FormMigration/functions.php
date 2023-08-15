<?php

use Give\Framework\Database\DB;

/**
 * This function is used to "redirect" shortcodes and blocks
 * to a migrated form ID, if one exists.
 *
 * ex: givewp_migrated_form_id($formId);
 * ex: givewp_migrated_form_id($formId, $atts['id']);
 *
 * @since 0.6.0
 *
 * @param $formId int $formId is used as an "output argument", meaning it is updated without needing to be returned.
 * @param $extraReference int[] Any additional references to update with the migrated form ID.
 *
 * @return void Note: $formId is an "output argument" - not a return value.
 */
function give_redirect_form_id(&$formId, &...$extraReference) {
    global $wpdb;

    $formId = absint(DB::get_var(
        DB::prepare(
            "
                    SELECT `form_id`
                    FROM `{$wpdb->prefix}give_formmeta`
                    WHERE `meta_key` = 'redirectedFormId'
                      AND `meta_value` = %d",
            $formId
        )
    ) ) ?: $formId;

    foreach($extraReference as &$reference) {
        $reference = $formId;
    }
}

/**
 * @param $formId
 *
 * @return bool
 */
function give_is_form_migrated($formId) {
    global $wpdb;

    return (bool) DB::get_var(
        DB::prepare(
            "
                    SELECT `form_id`
                    FROM `{$wpdb->prefix}give_formmeta`
                    JOIN `{$wpdb->posts}`
                        ON `{$wpdb->posts}`.`ID` = `{$wpdb->prefix}give_formmeta`.`form_id`
                    WHERE `post_status` != 'trash'
                      AND `meta_key` = 'migratedFormId'
                      AND `meta_value` = %d",
            $formId
        )
    );
}

/**
 * @param $formId
 *
 * @return bool
 */
function give_is_form_donations_transferred($formId) {
    global $wpdb;

    return (bool) DB::get_var(
        DB::prepare(
            "
                    SELECT `form_id`
                    FROM `{$wpdb->prefix}give_formmeta`
                    JOIN `{$wpdb->posts}`
                        ON `{$wpdb->posts}`.`ID` = `{$wpdb->prefix}give_formmeta`.`form_id`
                    WHERE `post_status` != 'trash'
                      AND `meta_key` = 'transferredFormId'
                      AND `meta_value` = %d",
            $formId
        )
    );
}
