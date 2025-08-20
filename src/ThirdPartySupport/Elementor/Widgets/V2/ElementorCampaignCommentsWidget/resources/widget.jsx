import {createRoot} from '@wordpress/element';
import CampaignComments from '@givewp/src/Campaigns/Blocks/CampaignComments/resources/shared/components/CampaignComments';

/**
 * @since 4.7.0
 */
export default class CampaignCommentsWidget extends elementorModules.frontend.handlers.Base {
    render() {
        const containers = document.querySelectorAll(`[data-id="${this.getID()}"] [data-givewp-campaign-comments]`);

        containers?.forEach((container) => {
            const root = createRoot(container);

            const attributes = JSON.parse(container.dataset?.attributes);
            const secondaryColor = container.dataset?.secondaryColor;

            root.render(<CampaignComments attributes={attributes} secondaryColor={secondaryColor} />);
        });
    }

    onInit() {
        this.render();
    }
}

/**
 * Register JS Handler for the Campaign Comments Widget
 */
window.addEventListener('elementor/frontend/init', () => {
    const addHandler = ($element) => {
        elementorFrontend.elementsHandler.addHandler(CampaignCommentsWidget, {$element});
    };

    elementorFrontend.hooks.addAction('frontend/element_ready/givewp_campaign_comments.default', addHandler);
});

