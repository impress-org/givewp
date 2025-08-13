import renderDonationForm from '@givewp/src/DonationForms/Blocks/DonationFormBlock/resources/app/renderDonationForm';

import './widget.scss';

/**
 * @unreleased
 */
export default class CampaignFormWidget extends elementorModules.frontend.handlers.Base {
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
 * Register JS Handler for the Test Widget
 *
 * When Elementor frontend was initiated, and the widget is ready, register the widet
 * JS handler.
 */
window.addEventListener('elementor/frontend/init', () => {
    const addHandler = ($element) => {
        elementorFrontend.elementsHandler.addHandler(CampaignFormWidget, {$element});
    };

    elementorFrontend.hooks.addAction('frontend/element_ready/givewp_campaign_form.default', addHandler);
});
