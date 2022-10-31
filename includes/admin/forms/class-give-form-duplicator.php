<?php
/**
 * The class contains logic to clone a donation form.
 *
 * @package     Give
 * @since       2.2.0
 * @copyright   Copyright (c) 2018, GiveWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @subpackage  Admin/Forms
 */

/**
 * Give_Form_Duplicator class
 */
class Give_Form_Duplicator
{

    /**
     * Clones the Form
     *
     * @since 2.2.0
     *
     * @return bool|int|WP_Error
     */
    static function handler($form_id)
    {
        $form_id = give_clean($form_id); // @codingStandardsIgnoreLine
        if ( ! is_numeric($form_id) || (int)$form_id <= 0) {
            return false;
        }
        $post_data = get_post($form_id);
        $current_user = wp_get_current_user();

        if (isset($post_data) && null !== $post_data) {
            $args = [
                'comment_status' => $post_data->comment_status,
                'ping_status' => $post_data->ping_status,
                'post_author' => $current_user->ID,
                'post_content' => $post_data->post_content,
                'post_date_gmt' => current_time('mysql', true),
                'post_excerpt' => $post_data->post_excerpt,
                'post_name' => $post_data->post_name,
                'post_parent' => $post_data->post_parent,
                'post_password' => $post_data->post_password,
                'post_status' => 'draft',
                'post_title' => $post_data->post_title,
                'post_type' => $post_data->post_type,
                'to_ping' => $post_data->to_ping,
                'menu_order' => $post_data->menu_order,
            ];

            // Get the ID of the cloned post.
            $duplicate_form_id = wp_insert_post($args);

            Give_Form_Duplicator::duplicate_taxonomies($duplicate_form_id, $post_data);
            Give_Form_Duplicator::duplicate_meta_data($duplicate_form_id, $post_data);
            Give_Form_Duplicator::reset_stats($duplicate_form_id);

            /**
             * Fire the action
             *
             * @since 2.2.0
             *
             * @param int $duplicate_form_id Duplicated form ID.
             * @param int $form_id           Form ID.
             */
            do_action('give_form_duplicated', $duplicate_form_id, $form_id);

            if ( ! is_wp_error($duplicate_form_id)) {
                return $duplicate_form_id;
            } else {
                return false;
            }

            exit;
        } else {
            return false;
        }
    }


    /**
     * Clone taxonomies
     *
     * @since  2.2.0
     * @access private
     *
     * @param int     $new_form_id New form ID.
     * @param WP_Post $old_form    Old form object.
     */
    static function duplicate_taxonomies($new_form_id, $old_form)
    {
        // Get the taxonomies of the post type `give_forms`.
        $taxonomies = get_object_taxonomies($old_form->post_type);

        foreach ($taxonomies as $taxonomy) {
            $post_terms = wp_get_object_terms(
                $old_form->ID,
                $taxonomy,
                [
                    'fields' => 'slugs',
                ]
            );

            wp_set_object_terms(
                $new_form_id,
                $post_terms,
                $taxonomy,
                false
            );
        }
    }


    /**
     * Clone meta data
     *
     * @since  2.2.0
     * @access private
     *
     * @param int     $new_form_id New Form ID.
     * @param WP_Post $old_form    Old form object.
     */
    static function duplicate_meta_data($new_form_id, $old_form)
    {
        global $wpdb;

        // Clone the metadata of the form.
        $post_meta_query = $wpdb->prepare("SELECT meta_key, meta_value FROM {$wpdb->formmeta} WHERE form_id=%s",
            $old_form->ID);

        $post_meta_data = $wpdb->get_results($post_meta_query); // WPCS: db call ok. WPCS: cache ok. WPCS: unprepared SQL OK.

        if ( ! empty($post_meta_data)) {
            $duplicate_query = "INSERT INTO {$wpdb->formmeta} (form_id, meta_key, meta_value) ";
            $duplicate_query_select = [];

            foreach ($post_meta_data as $meta_data) {
                $meta_key = $meta_data->meta_key;
                $meta_value = $meta_data->meta_value;
                $duplicate_query_select[] = $wpdb->prepare('SELECT %s, %s, %s', $new_form_id, $meta_key, $meta_value);
            }

            $duplicate_query .= implode(' UNION ALL ', $duplicate_query_select);

            $wpdb->query($duplicate_query); // WPCS: db call ok. WPCS: cache ok. WPCS: unprepared SQL OK.
        }
    }

    /**
     * Reset stats for cloned form
     *
     * @since  2.2.0
     * @access private
     *
     * @param int $new_form_id New Form ID.
     */
    static function reset_stats($new_form_id)
    {
        global $wpdb;

        $meta_keys = ['_give_form_sales', '_give_form_earnings'];

        /**
         * Fire the filter
         *
         * @since  2.2.0
         */
        $meta_keys = apply_filters('give_duplicate_form_reset_stat_meta_keys', $meta_keys);
        $meta_keys = 'meta_key=\'' . implode('\' OR meta_key=\'', $meta_keys) . '\'';

        $wpdb->query(
            $wpdb->prepare(
                "
                UPDATE $wpdb->formmeta
                SET meta_value=0
                WHERE form_id=%d
                AND ({$meta_keys})
                ",
                $new_form_id
            )
        );
    }
}

