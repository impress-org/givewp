<?php

namespace Give\FormBuilder\Routes;
use Give\Addon\View;
use Give\FormBuilder\ViewModels\FormBuilderViewModel;
use Give\Framework\EnqueueScript;

/**
 * Add form builder page route and scripts
 */
class RegisterFormBuilderPageRoute
{
    /**
     * Use add_submenu_page to register page within WP admin
     *
     * @unreleased
     *
     * @return void
     */
    public function __invoke()
    {
        add_submenu_page(
            'edit.php?post_type=give_forms',
            __('Visual Builder <span class="awaiting-mod">Alpha</span>', 'givewp'),
            __('Visual Builder <span class="awaiting-mod">Alpha</span>', 'givewp'),
            'manage_options',
            'campaign-builder',
            [$this, 'renderPage'],
            1
        );
    }

    /**
     * Render page with scripts
     *
     * @unreleased
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

        $formBuilderStorage->registerLocalizeData('storageData', $formBuilderViewModel->storageData($donationFormId));

        $formBuilderStorage->loadInFooter()->enqueue();

        (new EnqueueScript(
            '@givewp/form-builder/script',
            'packages/form-builder/build/' . $formBuilderViewModel->js(),
            GIVE_NEXT_GEN_DIR,
            GIVE_NEXT_GEN_URL,
            'give'
        ))->loadInFooter()->enqueue();

        wp_add_inline_script(
            '@givewp/form-builder/script',
            $formBuilderViewModel->attachShadowScript()
        );

        View::render('FormBuilder.admin-form-builder', [
            'shadowDomStyles' => $formBuilderViewModel->shadowDomStyles(),
        ]);
    }
}
