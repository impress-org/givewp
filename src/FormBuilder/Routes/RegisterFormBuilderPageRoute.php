<?php

namespace Give\FormBuilder\Routes;


use Give\FormBuilder\FormBuilderRouteBuilder;
use Give\FormBuilder\ViewModels\FormBuilderViewModel;
use Give\Framework\Views\View;
use Give\Helpers\Hooks;
use Give\Helpers\Language;
use Give\Log\Log;

use function wp_enqueue_style;

/**
 * Add form builder page route and scripts
 */
class RegisterFormBuilderPageRoute
{
    /**
     * Use add_submenu_page to register page within WP admin
     *
     * @since 3.0.0
     *
     * @return void
     */
    public function __invoke()
    {
        add_submenu_page(
            null, // do not display in menu, just register page
            'Visual Donation Form Builder', // ignored
            'Add Form', // ignored
            'manage_options',
            FormBuilderRouteBuilder::SLUG,
            [$this, 'renderPage'],
            1
        );

        add_action("admin_print_styles", function () {
            if (FormBuilderRouteBuilder::isRoute()) {
                wp_enqueue_style('givewp-design-system-foundation');

                $this->loadGutenbergScripts();

                wp_enqueue_style(
                    '@givewp/form-builder/style-app',
                    GIVE_PLUGIN_URL . 'build/formBuilderApp.css'
                );

                wp_enqueue_style(
                    'givewp-form-builder-admin-styles',
                    GIVE_PLUGIN_URL . 'src/FormBuilder/resources/css/admin-form-builder.css'
                );
            }
        });
    }

    /**
     * Render page with scripts
     *
     * @since 3.1.0 set translations for scripts
     * @since 3.0.0
     *
     * @return void
     */
    public function renderPage()
    {
        $formBuilderViewModel = new FormBuilderViewModel();

        $donationFormId = abs($_GET['donationFormID']);

        // validate form exists before proceeding
        // TODO: improve on this validation
        if (!get_post($donationFormId)) {
            wp_die(__('Donation form does not exist.'));
        }

        wp_enqueue_style(
            '@givewp/form-builder/registrars',
            GIVE_PLUGIN_URL . 'build/formBuilderRegistrars.css'
        );

        $registrarsScriptHandle = '@givewp/form-builder/registrars';
        wp_enqueue_script(
            $registrarsScriptHandle,
            $formBuilderViewModel->jsPathToRegistrars(),
            $this->getRegisteredFormBuilderJsDependencies(
                $formBuilderViewModel->jsRegistrarsDependencies()
            ),
            GIVE_VERSION,
            true
        );

        Language::setScriptTranslations($registrarsScriptHandle);

        /**
         * @since 3.1.0 set translations for scripts
         * @since 3.0.0
         * Using `wp_enqueue_script` instead of `new EnqueueScript` for more control over dependencies.
         * The `EnqueueScript` class discovers the dependencies from the associated `asset.php` file,
         * which might include dependencies that are not supported in some version of WordPress.
         * @link  https://github.com/impress-org/givewp-next-gen/pull/181#discussion_r1202686731
         */
        Hooks::doAction('givewp_form_builder_enqueue_scripts');

        $formBuilderScriptPath = '@givewp/form-builder/script';
        wp_enqueue_script(
            '@givewp/form-builder/script',
            $formBuilderViewModel->jsPathFromPluginRoot(),
            $this->getRegisteredFormBuilderJsDependencies(
                $formBuilderViewModel->jsDependencies()
            ),
            GIVE_VERSION,
            true
        );

        Language::setScriptTranslations($formBuilderScriptPath);

        wp_add_inline_script(
            '@givewp/form-builder/script',
            'window.giveStorageData = ' . json_encode($formBuilderViewModel->storageData($donationFormId)) . ';',
            'before'
        );

        wp_localize_script('@givewp/form-builder/script', 'onboardingTourData', [
            'actionUrl' => admin_url('admin-ajax.php?action=givewp_tour_completed'),
            'autoStartDesignTour' => !get_user_meta(get_current_user_id(), 'givewp-form-builder-design-tour-completed', true),
            'autoStartSchemaTour' => !get_user_meta(get_current_user_id(), 'givewp-form-builder-schema-tour-completed', true),
        ]);

        $migratedFormId = give_get_meta($donationFormId, 'migratedFormId', true);
        $transferredFormId = give_get_meta($donationFormId, 'transferredFormId', true);

        wp_localize_script('@givewp/form-builder/script', 'migrationOnboardingData', [
            'pluginUrl' => GIVE_PLUGIN_URL,
            'formId' => $donationFormId,
            'migrationActionUrl' => admin_url('admin-ajax.php?action=givewp_migration_hide_notice'),
            'transferActionUrl' => admin_url('admin-ajax.php?action=givewp_transfer_hide_notice'),
            'apiRoot' => esc_url_raw(rest_url('give-api/v2/admin/forms')),
            'apiNonce' => wp_create_nonce('wp_rest'),
            'isMigratedForm' => $migratedFormId,
            'isTransferredForm' => $transferredFormId,
            'showUpgradeDialog' => (bool)$migratedFormId && !(bool)give_get_meta(
                    $donationFormId,
                    'givewp-form-builder-migration-hide-notice',
                    true
                ),
            'transferShowNotice' => (bool)$migratedFormId && !(bool)$transferredFormId && !(bool)give_get_meta(
                    $donationFormId,
                    'givewp-form-builder-transfer-hide-notice',
                    true
                ),
        ]);

        wp_localize_script('@givewp/form-builder/script', 'goalNotificationData', [
            'actionUrl' => admin_url('admin-ajax.php?action=givewp_goal_hide_notice'),
            'isDismissed' => get_user_meta(get_current_user_id(), 'givewp-goal-notice-dismissed', true),
        ]);

        /**
         * @since 3.16.2
         */
        wp_localize_script('@givewp/form-builder/script', 'additionalPaymentGatewaysNotificationData', [
            'actionUrl' => admin_url('admin-ajax.php?action=givewp_additional_payment_gateways_hide_notice'),
            'isDismissed' => get_user_meta(get_current_user_id(), 'givewp-additional-payment-gateways-notice-dismissed', true),
        ]);

        View::render('FormBuilder.admin-form-builder');
    }

    /**
     * Load Gutenberg scripts and styles from core.
     *
     * @see   https://github.com/Automattic/isolated-block-editor/blob/trunk/examples/wordpress-php/iso-gutenberg.php
     *
     * @since 3.0.0
     */
    public function loadGutenbergScripts()
    {
        wp_enqueue_editor();

        // Gutenberg scripts
        wp_enqueue_script('wp-block-library');
        wp_enqueue_script('wp-format-library');
        wp_enqueue_script('wp-editor');

        // Gutenberg styles
        wp_enqueue_style('wp-edit-post');
        wp_enqueue_style('wp-format-library');
    }

    /**
     * Loop through the form builder js dependencies and check if they are registered before adding to enqueue_script.
     *
     * @since 3.0.0
     */
    protected function getRegisteredFormBuilderJsDependencies(array $formBuilderJsDependencies): array
    {
        $scripts = wp_scripts();

        return array_filter($formBuilderJsDependencies, static function ($dependency) use ($scripts) {
            $isRegistered = $scripts->query($dependency, 'registered');

            if (!$isRegistered) {
                Log::error(
                    sprintf(
                        'Script %s is not registered. Please check the script dependencies.',
                        $dependency
                    )
                );
            }

            return $isRegistered;
        });
    }
}
