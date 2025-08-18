import {createRoot} from '@wordpress/element';
import {CampaignGoalBlockApp} from '@givewp/src/Campaigns/Blocks/CampaignGoal/app';

/**
 * @since 4.7.0
 */
export default class CampaignGoalWidget extends elementorModules.frontend.handlers.Base {
    render() {
        const containers = document.querySelectorAll(`[data-id="${this.getID()}"] [data-givewp-campaign-goal]`);

        containers?.forEach((container) => {
            const root = createRoot(container);

            root.render(<CampaignGoalBlockApp campaignId={Number(container.getAttribute('data-id'))} />);
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
        elementorFrontend.elementsHandler.addHandler(CampaignGoalWidget, {$element});
    };

    elementorFrontend.hooks.addAction('frontend/element_ready/givewp_campaign_goal.default', addHandler);
});
