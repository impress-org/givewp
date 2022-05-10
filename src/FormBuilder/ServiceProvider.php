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
        add_action( 'rest_api_init', function () {
            register_rest_route( 'givewp/next-gen', '/form/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => function( \WP_REST_Request $request ) {
                    return get_post( $request->get_param('id') )->post_content;
                },
                'args' => [
                    'id' => [
                        'validate_callback' => function($param, $request, $key) {
                            return is_numeric( $param );
                        }
                    ],
                ],
            ) );
            register_rest_route( 'givewp/next-gen', '/form/(?P<id>\d+)', array(
                'methods' => 'POST',
                'callback' => function( \WP_REST_Request $request ) {
                    return wp_update_post([
                        'ID'           => $request->get_param('id'),
                        'post_content' => $request->get_param('blockData'),
                    ]);
                },
                'args' => [
                    'id' => [
                        'validate_callback' => function($param, $request, $key) {
                            return is_numeric( $param );
                        }
                    ],
                    'blockData' => [
                        'type' => 'string'
                    ]
                ],
            ) );
        } );

        add_action( 'admin_menu', function (){
            add_submenu_page(
                'edit.php?post_type=give_forms',
                __( 'Form Builder', 'givewp' ),
                __( 'Form Builder', 'givewp' ),
                'manage_options',
                'givenberg',
                function() {

                    if( ! isset( $_GET['donationFormID'] ) ) {
                        $currentURL = add_query_arg( $_SERVER['QUERY_STRING'], '', admin_url( 'edit.php') );
                        $forms = get_posts([
                            'numberposts' => -1,
                            'post_type'=>'give_forms'
                        ]);
                        echo '<ul>';
                        foreach( $forms as $form ) {
                        ?>
                            <li>
                        <a href="<?php echo add_query_arg( 'donationFormID', $form->ID, $currentURL); ?>"><?php echo $form->post_title; ?></a>
                            </li>
                            <?php
                        }
                        echo '</ul>';
                        return;
                    }

                    $manifest = json_decode( file_get_contents( GIVE_NEXT_GEN_DIR . 'packages/form-builder/build/asset-manifest.json' ) );
                    list( $css, $js ) = $manifest->entrypoints;

                    View::render( 'FormBuilder.admin-form-builder', [
                        'shadowDomStyles' => file_get_contents( trailingslashit(GIVE_NEXT_GEN_DIR) . 'packages/form-builder/build/' . $css ),
                    ]);

                    wp_enqueue_script( '@givewp/form-builder/storage', trailingslashit(GIVE_NEXT_GEN_URL) . 'src/FormBuilder/resources/js/storage.js' );
                    wp_localize_script( '@givewp/form-builder/storage', 'storageData', [
                        'resourceURL' => rest_url( 'givewp/next-gen/form/' . abs( $_GET['donationFormID'] ) ),
                        'blockData' => get_post( abs( $_GET['donationFormID'] ) )->post_content,
                    ]);

                    wp_enqueue_script( '@givewp/form-builder/script', trailingslashit(GIVE_NEXT_GEN_URL) . 'packages/form-builder/build/' . $js, [], false, true );
                    wp_add_inline_script( '@givewp/form-builder/script', "
                        document.getElementById('app').attachShadow({mode: 'open'})
                            .appendChild( document.getElementById('root') )
                            .appendChild( document.getElementById('shadowDomStyles') )
                    ");
                },
                1
            );
        } );
    }
}
