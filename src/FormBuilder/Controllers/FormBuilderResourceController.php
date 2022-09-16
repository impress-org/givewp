<?php

namespace Give\FormBuilder\Controllers;

use WP_Error;
use WP_HTTP_Response;
use WP_REST_Request;
use WP_REST_Response;

class FormBuilderResourceController
{
    /**
     * Get the form builder instance
     *
     * TODO: replace logic with form model
     * TODO: handle more validation and errors
     *
     * @unreleased
     *
     * @param  WP_REST_Request  $request
     * @return WP_Error|WP_HTTP_Response|WP_REST_Response
     */
    public function show(WP_REST_Request $request)
    {
        $formId = $request->get_param('id');

        if (!get_post($formId)) {
            return rest_ensure_response(new WP_Error(404, 'Form not found.'));
        }

        $formData = get_post($formId)->post_content;
        $formBuilderSettings = get_post_meta($formId, 'formBuilderSettings', true);

        return rest_ensure_response([
            'blocks' => $formData,
            'settings' => $formBuilderSettings
        ]);
    }

    /**
     * Update the form builder
     *
     * TODO: replace logic with form model
     * TODO: handle more validation and errors
     *
     * @unreleased
     *
     * @return WP_Error|WP_HTTP_Response|WP_REST_Response
     */
    public function update(WP_REST_Request $request)
    {
        $formId = $request->get_param('id');
        $formBuilderSettings = $request->get_param('settings');
        $data = $request->get_param('blocks');

        if (!get_post($formId)) {
            return rest_ensure_response(new WP_Error(404, 'Form not found.'));
        }

        $meta = update_post_meta($formId, 'formBuilderSettings', $formBuilderSettings);

        $post = wp_update_post([
            'ID' => $formId,
            'post_content' => $data,
            'post_title' => json_decode($formBuilderSettings, false)->formTitle,
        ]);

        return rest_ensure_response([
            'settings' => $meta,
            'form' => $post,
        ]);
    }
}
