<?php

namespace Give\DonationForms\Endpoints;

use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 2.19.0
 */
class FormActions extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/forms/(?P<action>[\S]+)';

    /**
     * @inheritDoc
     */
    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods'             => ['GET', 'POST', 'DELETE'],
                    'callback'            => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => [
                    'action' => [
                        'type'              => 'string',
                        'required'          => true,
                        'enum'              => [
                            'trash',
                            'restore',
                            'delete',
                            'duplicate'
                        ],
                    ],
                    'ids'    => [
                        'type'              => 'string',
                        'required'          => true,
                        'validate_callback' => function ($ids) {
                            foreach ($this->splitString($ids) as $id) {
                                if ( ! $this->validateInt($id)) {
                                    return false;
                                }
                            }

                            return true;
                        },
                    ]
                ],
            ]
        );
    }

    /**
     * @param  WP_REST_Request  $request
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request)
    {
        $ids = $this->splitString($request->get_param('ids'));
        $errors = [];
        $successes = [];
        $form = false;

        switch ($request->get_param('action')) {
            case 'trash':
                foreach ($ids as $id) {
                    $form = wp_trash_post($id);
                    $form ? $successes[] = $form : $errors[] = $form;
                }

                break;

            case 'restore':
                foreach ($ids as $id) {
                    $form = wp_untrash_post($id);
                    $form ? $successes[] = $form : $errors[] = $form;
                }

                break;


            case 'delete':
                foreach ($ids as $id) {
                    $form = wp_delete_post($id);
                    give()->form_meta->delete_all_meta($id);
                    $form ? $successes[] = $form : $errors[] = $form;
                }

                break;

            case 'duplicate':
                require_once(GIVE_PLUGIN_DIR . '/includes/admin/forms/class-give-form-duplicator.php');

                foreach ($ids as $id) {
                    $form = \Give_Form_Duplicator::handler($id);
                    $form ? $successes[] = $form : $errors[] = $form;
                }

                break;
        }

        return new WP_REST_Response(array('errors' => $errors, 'successes' => $successes));
    }


    /**
     * Split string
     *
     * @param  string  $ids
     *
     * @return string[]
     */
    protected function splitString($ids)
    {
        if (strpos($ids, ',')) {
            return array_map('trim', explode(',', $ids));
        }

        return [trim($ids)];
    }
}
