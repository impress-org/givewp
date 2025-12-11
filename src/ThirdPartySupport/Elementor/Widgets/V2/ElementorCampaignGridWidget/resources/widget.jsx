import {createRoot} from '@wordpress/element';
import CampaignGridBlockApp from '@givewp/src/Campaigns/Blocks/CampaignGrid/app/index';

/**
 * @since 4.7.0
 */
export default class CampaignGridWidget extends elementorModules.frontend.handlers.Base {
    render() {
        const containers = document.querySelectorAll(`[data-id="${this.getID()}"] [data-givewp-campaign-grid]`);

        containers?.forEach((container) => {
            const root = createRoot(container);

            const attributes = JSON.parse(container.dataset?.attributes);
            console.log(attributes);

            root.render(<CampaignGridBlockApp attributes={attributes} />);
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
        elementorFrontend.elementsHandler.addHandler(CampaignGridWidget, {$element});
    };

    elementorFrontend.hooks.addAction('frontend/element_ready/givewp_campaign_grid.default', addHandler);
});
