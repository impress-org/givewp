import renderDonationForm from '@givewp/src/DonationForms/Blocks/DonationFormBlock/resources/app/renderDonationForm';

/**
 * @since 4.7.1
 */
export default class GiveFormWidget extends elementorModules.frontend.handlers.Base {
    render() {
        const roots = document.querySelectorAll(`[data-id="${this.getID()}"] .root-data-givewp-embed`);

        roots?.forEach((root) => {
            renderDonationForm(root);
        });
    }

    onInit() {
        this.render();
    }
}

/**
 * Register JS Handler
 *
 * When Elementor frontend was initiated, and the widget is ready, register the widet
 * JS handler.
 */
window.addEventListener('elementor/frontend/init', () => {
    const addHandler = ($element) => {
        elementorFrontend.elementsHandler.addHandler(GiveFormWidget, {$element});
    };

    elementorFrontend.hooks.addAction('frontend/element_ready/Give Form.default', addHandler);
});
