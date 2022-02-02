<?php

namespace Give\DonationForms\Endpoints;

use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
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
                        'validate_callback' => function ($action) {
                            return in_array($action, ['trash', 'restore', 'delete', 'duplicate'], true);
                        },
                    ],
                    'ids'    => [
                        'type'              => 'string',
                        'required'          => true,
                        'validate_callback' => [$this, 'validateIds'],
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

        switch ($request->get_param('action')) {
            case 'trash':
                foreach ($ids as $id) {
                    wp_trash_post($id);
                }

                break;

            case 'restore':
                foreach ($ids as $id) {
                    wp_untrash_post($id);
                }

                break;


            case 'delete':
                foreach ($ids as $id) {
                    wp_delete_post($id, true);
                }

                break;

            case 'duplicate':
                require_once(GIVE_PLUGIN_DIR . '/includes/admin/forms/class-give-form-duplicator.php');

                foreach ($ids as $id) {
                    \Give_Form_Duplicator::handler($id);
                }

                break;
        }

        return new WP_REST_Response();
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

    /**
     * @param  string  $ids  '1,2,3'
     *
     * @return bool
     */
    protected function validateIds($ids)
    {
        foreach ($this->splitString($ids) as $id) {
            if ( ! $this->validateInt($id)) {
                return false;
            }
        }

        return true;
    }
}
