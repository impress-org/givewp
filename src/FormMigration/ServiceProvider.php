<?php

namespace Give\FormMigration;

use Give\FormMigration\Commands\MigrationCommand;
use Give\FormMigration\Controllers\MigrationController;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use WP_CLI;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @since 0.1.0
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        give()->singleton(Pipeline::class, function() {
            return new Pipeline([
                Steps\MigrateMeta::class,
                Steps\FormTitle::class,
                Steps\FormTemplate\ClassicTemplateSettings::class,
                Steps\FormTemplate\SequoiaTemplateSettings::class,
                Steps\FormTemplate\LegacyTemplateSettings::class,
                Steps\DonationOptions::class,
                Steps\FormFields::class,
                Steps\FormFields\LoginRegistration::class,
                Steps\FormFields\CompanyDonations::class,
                Steps\DonationGoal::class,
                Steps\TermsAndConditions::class,
                Steps\FormGrid::class,
                Steps\OfflineDonations::class,
                Steps\EmailSettings::class,
            ]);
        });
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        $this->registerRoutes();
        $this->registerCommands();
    }

    protected function registerRoutes()
    {
        add_action('rest_api_init', function() {
            register_rest_route('give-api/v2', 'admin/forms/migrate', [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => function (WP_REST_Request $request) {
                    $controller = new MigrationController($request);
                    $formIdV2 = $request->get_param('ids')[0];
                    return $controller($formIdV2);
                },
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
                'args' => [
                    'ids' => [
                        'type' => 'integer',
                        'sanitize_callback' => function($value) {
                            return array_map('intval', explode(',', $value));
                        },
                        'description' => __('The ID of the form (v2) to migrate to v3.', 'givewp'),
                    ],
                ],
            ]);
        }, 9);
    }

    protected function registerCommands()
    {
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            error_reporting( E_ALL & ~E_DEPRECATED );
            WP_CLI::add_command('givewp form:migrate', MigrationCommand::class);
        }
    }
}
