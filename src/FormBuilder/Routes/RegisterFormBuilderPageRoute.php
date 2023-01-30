<?php

namespace Give\FormBuilder\Routes;
use Give\Addon\View;
use Give\FormBuilder\FormBuilderRouteBuilder;
use Give\FormBuilder\ViewModels\FormBuilderViewModel;
use Give\Framework\EnqueueScript;

use function wp_enqueue_style;

/**
 * Add form builder page route and scripts
 */
class RegisterFormBuilderPageRoute
{
    /**
     * Use add_submenu_page to register page within WP admin
     *
     * @since 0.1.0
     *
     * @return void
     */
    public function __invoke()
    {
        $pageTitle = __('Visual Donation Form Builder', 'givewp');
        $menuTitle = __('Add Next Gen Form', 'givewp');
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

        add_action("admin_print_styles", static function () {
            if (FormBuilderRouteBuilder::isRoute()) {
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

        $formBuilderStorage = (new EnqueueScript(
            '@givewp/form-builder/storage',
            'src/FormBuilder/resources/js/storage.js',
            GIVE_NEXT_GEN_DIR,
            GIVE_NEXT_GEN_URL,
            'give'
        ));

        $formBuilderStorage->dependencies(['jquery'])->registerLocalizeData(
            'storageData',
            $formBuilderViewModel->storageData($donationFormId)
        );

        $formBuilderStorage->loadInFooter()->enqueue();

        (new EnqueueScript(
            '@givewp/form-builder/script',
            $formBuilderViewModel->jsPathFromRoot(),
            GIVE_NEXT_GEN_DIR,
            GIVE_NEXT_GEN_URL,
            'give'
        ))->loadInFooter()->enqueue();

        View::render('FormBuilder.admin-form-builder');
    }
}
