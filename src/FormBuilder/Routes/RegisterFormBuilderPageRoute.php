<?php

namespace Give\FormBuilder\Routes;

use Give\Addon\View;
use Give\FormBuilder\FormBuilderRouteBuilder;
use Give\FormBuilder\ViewModels\FormBuilderViewModel;
use Give\Framework\EnqueueScript;
use Give\Helpers\Hooks;
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
     * @since 0.4.0 enqueue form builder styles
     * @since 0.1.0
     *
     * @return void
     */
    public function __invoke()
    {
        $pageTitle = __('Visual Donation Form Builder', 'givewp');
        $menuTitle = __('Add v3 Form', 'givewp');
        $version = __('Beta', 'givewp');

        add_submenu_page(
            'edit.php?post_type=give_forms',
            "$pageTitle&nbsp;<span class='awaiting-mod'>$version</span>",
            "$menuTitle&nbsp;<span class='awaiting-mod'>$version</span>",
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
                    GIVE_NEXT_GEN_URL . 'build/formBuilderApp.css'
                );

                wp_enqueue_style(
                    'givewp-form-builder-admin-styles',
                    GIVE_NEXT_GEN_URL . 'src/FormBuilder/resources/css/admin-form-builder.css'
                );
            }
        });
    }

    /**
     * Render page with scripts
     *
     * @since 0.4.0 Add support for custom form extensions
     * @since 0.1.0
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

        wp_enqueue_script(
            '@givewp/form-builder/registrars',
            $formBuilderViewModel->jsPathToRegistrars(),
            [],
            GIVE_NEXT_GEN_VERSION,
            true
        );

        (new EnqueueScript(
            '@givewp/form-builder/storage',
            'src/FormBuilder/resources/js/storage.js',
            GIVE_NEXT_GEN_DIR,
            GIVE_NEXT_GEN_URL,
            'give'
        ))
            ->dependencies(['jquery'])
            ->registerLocalizeData('storageData', $formBuilderViewModel->storageData($donationFormId))
            ->loadInFooter()
            ->enqueue();

        /**
         * @since 0.4.0
         * Using `wp_enqueue_script` instead of `new EnqueueScript` for more control over dependencies.
         * The `EnqueueScript` class discovers the dependencies from the associated `asset.php` file,
         * which might include dependencies that are not supported in some version of WordPress.
         * @link https://github.com/impress-org/givewp-next-gen/pull/181#discussion_r1202686731
         */
        Hooks::doAction('givewp_form_builder_enqueue_scripts');

        wp_enqueue_script(
            '@givewp/form-builder/script',
            $formBuilderViewModel->jsPathFromPluginRoot(),
            $this->getRegisteredFormBuilderJsDependencies(
                $formBuilderViewModel->jsDependencies()
            ),
            GIVE_NEXT_GEN_VERSION,
            true
        );

        wp_localize_script('@givewp/form-builder/script', 'onboardingTourData', [
            'actionUrl' => admin_url('admin-ajax.php?action=givewp_tour_completed'),
            'autoStartTour' => !get_user_meta(get_current_user_id(), 'givewp-form-builder-tour-completed', true),
        ]);

        View::render('FormBuilder.admin-form-builder');
    }

    /**
     * Load Gutenberg scripts and styles from core.
     *
     * @see https://github.com/Automattic/isolated-block-editor/blob/trunk/examples/wordpress-php/iso-gutenberg.php
     *
     * @since 0.4.0
     */
    public function loadGutenbergScripts()
    {
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
     * @since 0.4.0
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
