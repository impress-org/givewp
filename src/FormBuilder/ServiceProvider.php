<?php

namespace Give\FormBuilder;

use Give\DonationForms\Models\DonationForm;
use Give\FormBuilder\Actions\ConvertGlobalDefaultOptionsToDefaultBlocks;
use Give\FormBuilder\Actions\DequeueAdminScriptsInFormBuilder;
use Give\FormBuilder\Actions\DequeueAdminStylesInFormBuilder;
use Give\FormBuilder\Actions\UpdateDonorCommentsMeta;
use Give\FormBuilder\Actions\UpdateEmailSettingsMeta;
use Give\FormBuilder\Actions\UpdateEmailTemplateMeta;
use Give\FormBuilder\Actions\UpdateFormExcerpt;
use Give\FormBuilder\Actions\UpdateFormGridMeta;
use Give\FormBuilder\EmailPreview\Routes\RegisterEmailPreviewRoutes;
use Give\FormBuilder\Routes\CreateFormRoute;
use Give\FormBuilder\Routes\EditFormRoute;
use Give\FormBuilder\Routes\RegisterFormBuilderPageRoute;
use Give\FormBuilder\Routes\RegisterFormBuilderRestRoutes;
use Give\FormBuilder\ValueObjects\EditorMode;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

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
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        Hooks::addAction('rest_api_init', RegisterFormBuilderRestRoutes::class);

        Hooks::addAction('rest_api_init', RegisterEmailPreviewRoutes::class);

        Hooks::addAction('admin_init', CreateFormRoute::class);

        Hooks::addAction('admin_init', EditFormRoute::class);

        Hooks::addAction('admin_menu', RegisterFormBuilderPageRoute::class);

        Hooks::addAction('admin_print_scripts', DequeueAdminScriptsInFormBuilder::class);

        Hooks::addAction('admin_print_styles', DequeueAdminStylesInFormBuilder::class);

        /** Integrates the "Add v3 Form" button with the Donation Forms table. */
        add_action('admin_enqueue_scripts', static function () {
            wp_localize_script('give-admin-donation-forms', 'GiveNextGen', [
                'newFormUrl' => FormBuilderRouteBuilder::makeCreateFormRoute()->getUrl(),
            ]);
        });

        add_action('givewp_form_builder_updated', static function (DonationForm $form) {
            give(UpdateFormGridMeta::class)->__invoke($form);
            give(UpdateEmailSettingsMeta::class)->__invoke($form);
            give(UpdateEmailTemplateMeta::class)->__invoke($form);
            give(UpdateDonorCommentsMeta::class)->__invoke($form);
        });

        Hooks::addAction('givewp_form_builder_new_form', ConvertGlobalDefaultOptionsToDefaultBlocks::class);

        $this->setupOnboardingTour();
    }

    protected function setupOnboardingTour()
    {
        add_action('wp_ajax_givewp_tour_completed', static function () {
            $mode = new EditorMode($_POST['mode']);
            add_user_meta(get_current_user_id(), "givewp-form-builder-$mode-tour-completed", time(), true);
        });

        add_action('wp_ajax_givewp_migration_hide_notice', static function () {
            give_update_meta((int)$_GET['formId'], 'givewp-form-builder-migration-hide-notice', time(), true);
        });

        add_action('wp_ajax_givewp_transfer_hide_notice', static function () {
            give_update_meta((int)$_GET['formId'], 'givewp-form-builder-transfer-hide-notice', time(), true);
        });

        add_action('wp_ajax_givewp_goal_hide_notice', static function () {
            add_user_meta(get_current_user_id(), 'givewp-goal-notice-dismissed', time(), true);
        });
    }
}
