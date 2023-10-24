<?php

use Give\FormBuilder\EmailPreview\Controllers\SendEmailPreview;
use Give\FormBuilder\EmailPreview\Controllers\ShowEmailPreview;

return [

    /*
    |--------------------------------------------------------------------------
    | Show the HTML of the email preview
    |--------------------------------------------------------------------------
    |
    */

    'show' => [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => [give(ShowEmailPreview::class), '__invoke'],
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
        'args' => [
            'email_type' => [
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'form_id' => [
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'absint',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Send the email preview to a specified address
    |--------------------------------------------------------------------------
    |
    */

    'send' => [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => [give(SendEmailPreview::class), '__invoke'],
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
        'args' => [
            'email_type' => [
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'email_address' => [
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_email',
            ],
        ],
    ],
];
