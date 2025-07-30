// @ts-nocheck
import {createRoot} from '@wordpress/element';
import DonationFormBlockApp from '@givewp/src/DonationForms/Blocks/DonationFormBlock/resources/app';

import './widget.scss';

export default class CampaignFormWidget extends elementorModules.frontend.handlers.Base {
    render() {
        const roots = document.querySelectorAll('.root-data-givewp-embed');

        roots.forEach((root) => {
            let dataSrcUrl = root.getAttribute('data-src');
            const locale = root.getAttribute('data-form-locale');
            if (locale) {
                const url = new URL(dataSrcUrl);
                url.searchParams.set('locale', locale);
                dataSrcUrl = url.toString();
            }

            const dataSrc = dataSrcUrl;
            const embedId = root.getAttribute('data-givewp-embed-id');
            const formFormat = root.getAttribute('data-form-format');
            const openFormButton = root.getAttribute('data-open-form-button');
            const formUrl = root.getAttribute('data-form-url');
            const formViewUrl = root.getAttribute('data-form-view-url');

            createRoot(root).render(
                <DonationFormBlockApp
                    openFormButton={openFormButton}
                    formFormat={formFormat}
                    dataSrc={dataSrc}
                    embedId={embedId}
                    formUrl={formUrl}
                    formViewUrl={formViewUrl}
                />
            );
        });
    }

    onInit() {
        console.log('onInit');
        this.render();
    }

    onElementChange(propertyName) {
        console.log('onElementChange', propertyName);
        console.log(this.getElementSettings());
    }
}

/**
 * Register JS Handler for the Test Widget
 *
 * When Elementor frontend was initiated, and the widget is ready, register the widet
 * JS handler.
 */
window.addEventListener( 'elementor/frontend/init', () => {
	const addHandler = ( $element ) => {
		elementorFrontend.elementsHandler.addHandler( CampaignFormWidget, { $element } );
	};

	elementorFrontend.hooks.addAction( 'frontend/element_ready/givewp_campaign_form.default', addHandler );
} );
