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
use Give\Framework\Permissions\Facades\UserPermissions;
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
     * @since TBD The forms list localizes its own newFormUrl, so the GiveNextGen script data is gone.
     *
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

        add_action('givewp_form_builder_updated', static function (DonationForm $form) {
            give(UpdateFormGridMeta::class)->__invoke($form);
            give(UpdateEmailSettingsMeta::class)->__invoke($form);
            give(UpdateEmailTemplateMeta::class)->__invoke($form);
            give(UpdateDonorCommentsMeta::class)->__invoke($form);
        });

        Hooks::addAction('givewp_form_builder_new_form', ConvertGlobalDefaultOptionsToDefaultBlocks::class);

        $this->setupOnboardingTour();
    }

    /**
     * @since TBD Verify the nonce and the capability, and sanitize the request input.
     */
    protected function setupOnboardingTour()
    {
        add_action('wp_ajax_givewp_tour_completed', static function () {
            self::verifyAjaxRequest();

            $mode = new EditorMode(isset($_POST['mode']) ? sanitize_text_field(wp_unslash($_POST['mode'])) : ''); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified by ServiceProvider::verifyAjaxRequest() at the top of this handler.
            add_user_meta(get_current_user_id(), "givewp-form-builder-$mode-tour-completed", time(), true);
        });

        add_action('wp_ajax_givewp_migration_hide_notice', static function () {
            self::verifyAjaxRequest();

            give_update_meta(self::getFormIdFromAjaxRequest(), 'givewp-form-builder-migration-hide-notice', time(), true);
        });

        add_action('wp_ajax_givewp_transfer_hide_notice', static function () {
            self::verifyAjaxRequest();

            give_update_meta(self::getFormIdFromAjaxRequest(), 'givewp-form-builder-transfer-hide-notice', time(), true);
        });

        add_action('wp_ajax_givewp_goal_hide_notice', static function () {
            self::verifyAjaxRequest();

            add_user_meta(get_current_user_id(), 'givewp-goal-notice-dismissed', time(), true);
        });

        /**
         * @since 3.16.2
         */
        add_action('wp_ajax_givewp_additional_payment_gateways_hide_notice', static function () {
            self::verifyAjaxRequest();

            add_user_meta(get_current_user_id(), 'givewp-additional-payment-gateways-notice-dismissed', time(), true);
        });
    }

    /**
     * Stops the request unless it carries the form builder nonce and comes from a user who can edit forms.
     *
     * @since TBD
     */
    private static function verifyAjaxRequest()
    {
        check_ajax_referer(FormBuilderRouteBuilder::AJAX_NONCE_ACTION);

        if (!current_user_can(UserPermissions::donationForms()->editCap())) {
            wp_die('-1', '', 403);
        }
    }

    /**
     * @since TBD
     */
    private static function getFormIdFromAjaxRequest(): int
    {
        return isset($_GET['formId']) ? absint($_GET['formId']) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce verified by ServiceProvider::verifyAjaxRequest() before this is called.
    }
}
