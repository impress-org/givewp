<?php

namespace Give\FormMigration;

use Give\DonationForms\V2\Models\DonationForm as DonationFormV2;
use Give\FormMigration\Commands\MigrationCommand;
use Give\FormMigration\Commands\TransferCommand;
use Give\FormMigration\Controllers\MigrationController;
use Give\FormMigration\Controllers\TransferController;
use Give\FormMigration\DataTransferObjects\TransferOptions;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use WP_CLI;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @since 3.0.0
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        give()->singleton(Pipeline::class, function () {
            return new Pipeline([
                Steps\MigrateMeta::class,
                Steps\FormTitle::class,
                Steps\FormTemplate\ClassicTemplateSettings::class,
                Steps\FormTemplate\SequoiaTemplateSettings::class,
                Steps\FormTemplate\LegacyTemplateSettings::class,
                Steps\DonationOptions::class,
                Steps\RecurringDonationOptions::class,
                Steps\FormFields::class,
                Steps\FormFields\LoginRegistration::class,
                Steps\FormFields\CompanyDonations::class,
                Steps\DonationGoal::class,
                Steps\TermsAndConditions::class,
                Steps\FormTaxonomies::class,
                Steps\FormGrid::class,
                Steps\FormFieldManager::class,
                Steps\OfflineDonations::class,
                Steps\PaymentGateways::class,
                Steps\EmailSettings::class,
                Steps\FormMeta::class,
                Steps\PdfSettings::class,
                Steps\FeeRecovery::class,
                Steps\ConstantContact::class,
                Steps\PerFormGateways::class,
                Steps\Mailchimp::class,
                Steps\FundsAndDesignations::class,
                Steps\GiftAid::class,
                Steps\FormFeaturedImage::class,
                Steps\FormExcerpt::class,
                Steps\ConvertKit::class,
                Steps\ActiveCampaign::class,
                Steps\DoubleTheDonation::class,
                Steps\CurrencySwitcher::class,
                Steps\RazorpayPerFormSettings::class,
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
        add_action('rest_api_init', function () {
            // give-api/v2/admin/forms/migrate
            register_rest_route('give-api/v2', 'admin/forms/migrate/(?P<id>\d+)', [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => function (WP_REST_Request $request) {
                    return (new MigrationController($request))(
                        DonationFormV2::find($request->get_param('id'))
                    );
                },
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
                'args'                => [
                    'id' => [
                        'type'              => 'integer',
                        'sanitize_callback' => 'absint',
                        'description'       => __('The ID of the form (v2) to migrate to v3.', 'give'),
                    ],
                ],
            ]);

            // give-api/v2/admin/forms/transfer
            register_rest_route('give-api/v2', 'admin/forms/transfer', [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => function (WP_REST_Request $request) {
                    return (new TransferController($request))(
                        DonationFormV2::find($request->get_param('formId')),
                        TransferOptions::fromRequest($request)
                    );
                },
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
                'args'                => [
                    'formId'    => [
                        'type'              => 'integer',
                        'sanitize_callback' => function ($value) {
                            return intval($value);
                            // return array_map('intval', explode(',', $value));
                        },
                        'description'       => __('The ID of the form (v3) to transfer donations (from v2).', 'give'),
                    ],
                    'changeUrl' => [
                        'type'     => 'boolean',
                        'required' => false,
                        'default'  => true,
                    ],
                    'delete'    => [
                        'type'     => 'boolean',
                        'required' => true,
                    ],
                    'redirect'  => [
                        'type'     => 'boolean',
                        'required' => false,
                        'default'  => true,
                    ],
                ],
            ]);
        }, 9);
    }

    protected function registerCommands()
    {
        if (defined('WP_CLI') && WP_CLI) {
            error_reporting(E_ALL & ~E_DEPRECATED);
            WP_CLI::add_command('givewp form:migrate', MigrationCommand::class);
            WP_CLI::add_command('givewp form:transfer', TransferCommand::class);
        }
    }
}
