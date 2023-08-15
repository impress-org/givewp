<?php

namespace Give\FormBuilder\Routes;

use Give\FormBuilder\Controllers\FormBuilderResourceController;
use Give\FormBuilder\ValueObjects\FormBuilderRestRouteConfig;
use WP_REST_Request;
use WP_REST_Server;

class RegisterFormBuilderRestRoutes
{
    /**
     * @var FormBuilderResourceController
     */
    protected $formBuilderResourceController;

    /**
     * @since 3.0.0
     */
    public function __construct(
        FormBuilderResourceController $formBuilderResourceController
    ) {
        $this->formBuilderResourceController = $formBuilderResourceController;
    }

    /**
     * @since 3.0.0
     *
     * @return void
     */
    public function __invoke()
    {
        $this->registerGetForm(FormBuilderRestRouteConfig::NAMESPACE, FormBuilderRestRouteConfig::PATH);
        $this->registerPostForm(FormBuilderRestRouteConfig::NAMESPACE, FormBuilderRestRouteConfig::PATH);
    }

    /**
     * Get Request
     *
     * @since 3.0.0
     *
     * @return void
     */
    public function registerGetForm(string $namespace, string $route)
    {
        register_rest_route($namespace, $route, [
            'methods' => WP_REST_Server::READABLE,
            'callback' => function (WP_REST_Request $request) {
                return $this->formBuilderResourceController->show($request);
            },
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
            'args' => [
                'id' => [
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);
    }

    /**
     * Post Request
     *
     * @since 3.0.0
     *
     * @return void
     */
    public function registerPostForm(string $namespace, string $route)
    {
        register_rest_route($namespace, $route, [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => function (WP_REST_Request $request) {
                return $this->formBuilderResourceController->update($request);
            },
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
            'args' => [
                'id' => [
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
                'blocks' => [
                    'type' => 'string',
                ],
            ],
        ]);
    }
}
