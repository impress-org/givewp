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
                    'methods'             => ['POST', 'UPDATE', 'DELETE'],
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
                            'duplicate',
                            'edit',
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
                    ],
                    'author' => [
                        'type'              => 'string',
                        'required'          => 'false',
                    ],
                    'status' => [
                        'type'              => 'string',
                        'required'          => 'false',
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
                    !empty($form) ? $successes[] = $id : $errors[] = $id;
                }

                break;

            case 'restore':
                foreach ($ids as $id) {
                    $form = wp_untrash_post($id);
                    !empty($form) ? $successes[] = $id : $errors[] = $id;
                }

                break;


            case 'delete':
                foreach ($ids as $id) {
                    $form = wp_delete_post($id);
                    give()->form_meta->delete_all_meta($id);
                    !empty($form) ? $successes[] = $form : $errors[] = $form;
                }

                break;

            case 'duplicate':
                require_once(GIVE_PLUGIN_DIR . '/includes/admin/forms/class-give-form-duplicator.php');

                foreach ($ids as $id) {
                    $form = \Give_Form_Duplicator::handler($id);
                    $form ? $successes[] = $form : $errors[] = $form;
                }

                break;

            case 'edit':
                $author = $request->get_param('author');
                $status = $request->get_param('status');
                $update_args = [];
                $author ? $update_args['post_author'] = $author : null;
                $status ? $update_args['post_status'] = $status : null;
                foreach ($ids as $id) {
                    $form = wp_update_post(array_merge($update_args, ['ID' => $id]));
                    !empty($form) ? $successes[] = $id : $errors[] = $id;
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
