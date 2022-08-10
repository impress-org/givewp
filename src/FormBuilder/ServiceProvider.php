<?php

namespace Give\FormBuilder;

use Give\Addon\View;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @unreleased
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        add_action('rest_api_init', function () {
            register_rest_route('givewp/next-gen', '/form/(?P<id>\d+)', [
                'methods' => 'GET',
                'callback' => function (\WP_REST_Request $request) {
                    return [
                        'blocks' => get_post($request->get_param('id'))->post_content,
                        'settings' => get_post_meta($request->get_param('id'), 'formBuilderSettings', true),
                    ];
                },
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
                'args' => [
                    'id' => [
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        },
                    ],
                ],
            ]);
            register_rest_route('givewp/next-gen', '/form/(?P<id>\d+)', [
                'methods' => 'POST',
                'callback' => function (\WP_REST_Request $request) {
                    $settings = json_decode($request->get_param('settings'));
                    $meta = update_post_meta($request->get_param('id'), 'formBuilderSettings',
                        $request->get_param('settings'));
                    $post = wp_update_post([
                        'ID' => $request->get_param('id'),
                        'post_content' => $request->get_param('blocks'),
                        'post_title' => $settings->formTitle,
                    ]);

                    return [
                        $meta,
                        $post,
                    ];
                },
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
                'args' => [
                    'id' => [
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        },
                    ],
                    'blockData' => [
                        'type' => 'string',
                    ],
                ],
            ]);
        });

        add_action('admin_init', function () {
            if (isset($_GET['page']) && 'campaign-builder' === $_GET['page']) {
                // Little hack for alpha users to make sure the form builder is loaded.
                if ( ! isset($_GET['donationFormID'])) {
                    wp_redirect('edit.php?post_type=give_forms&page=campaign-builder&donationFormID=new');
                    exit();
                }
                if ('new' === $_GET['donationFormID']) {
                    $newPostID = wp_insert_post([
                        'post_type' => 'give_forms',
                        'post_status' => 'publish',
                        'post_content' => json_encode(null),
                    ]);

                    wp_update_post([
                        'ID' => $newPostID,
                        'post_title' => "Next Gen Donation Form ID:$newPostID",
                    ]);

                    wp_redirect('edit.php?post_type=give_forms&page=campaign-builder&donationFormID=' . $newPostID);
                    exit();
                }
            }
        });

        add_action('admin_init', function () {
            if (isset($_GET['post']) && isset($_GET['action']) && 'edit' === $_GET['action']) {
                $post = get_post(abs($_GET['post']));
                if ('give_forms' === $post->post_type && $post->post_content) {
                    wp_redirect('edit.php?post_type=give_forms&page=campaign-builder&donationFormID=' . $post->ID);
                    exit();
                }
            }
        });

        add_action('admin_menu', function () {
            add_submenu_page(
                'edit.php?post_type=give_forms',
                __('Visual Builder <span class="awaiting-mod">Alpha</span>', 'givewp'),
                __('Visual Builder <span class="awaiting-mod">Alpha</span>', 'givewp'),
                'manage_options',
                'campaign-builder',
                function () {
                    $manifest = json_decode(file_get_contents(GIVE_NEXT_GEN_DIR . 'packages/form-builder/build/asset-manifest.json'));
                    [$css, $js] = $manifest->entrypoints;

                    View::render('FormBuilder.admin-form-builder', [
                        'shadowDomStyles' => file_get_contents(trailingslashit(GIVE_NEXT_GEN_DIR) . 'packages/form-builder/build/' . $css),
                    ]);

                    wp_enqueue_script('@givewp/form-builder/storage',
                        trailingslashit(GIVE_NEXT_GEN_URL) . 'src/FormBuilder/resources/js/storage.js');
                    wp_localize_script('@givewp/form-builder/storage', 'giveCurrency', [
                        'currency' => give_get_currency(),
                    ]);
                    wp_localize_script('@givewp/form-builder/storage', 'storageData', [
                        'resourceURL' => rest_url('givewp/next-gen/form/' . abs($_GET['donationFormID'])),
                        'nonce' => wp_create_nonce('wp_rest'),
                        'blockData' => get_post(abs($_GET['donationFormID']))->post_content,
                        'settings' => get_post_meta(abs($_GET['donationFormID']), 'formBuilderSettings', true),
                    ]);

                    wp_enqueue_script('@givewp/form-builder/script',
                        trailingslashit(GIVE_NEXT_GEN_URL) . 'packages/form-builder/build/' . $js, [], false, true);
                    wp_add_inline_script('@givewp/form-builder/script', "
                        document.getElementById('app').attachShadow({mode: 'open'})
                            .appendChild( document.getElementById('root') )
                            .appendChild( document.getElementById('shadowDomStyles') )
                    ");
                },
                1
            );
        });
    }
}
