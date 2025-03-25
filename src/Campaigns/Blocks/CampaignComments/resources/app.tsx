import {createRoot} from '@wordpress/element';
import CampaignComments from './shared/components/CampaignComments';

const roots = document.querySelectorAll('[data-givewp-campaign-comments]');

if (roots) {
    roots.forEach((root) => {
        const attributes = root.getAttribute('data-attributes');
        const primaryColor = root.getAttribute('data-primary-color');

        return createRoot(root).render(
            <CampaignComments attributes={JSON.parse(attributes)} primaryColor={primaryColor} />
        );
    });
}
