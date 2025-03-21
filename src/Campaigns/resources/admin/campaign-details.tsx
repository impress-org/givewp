import {createRoot} from '@wordpress/element';
import CampaignsDetailsPage from './components/CampaignDetailsPage';

const container = document.getElementById('give-admin-campaigns-root');
const urlParams = new URLSearchParams(window.location.search);

if (container) {
    const root = createRoot(container);
    root.render(<CampaignsDetailsPage campaignId={urlParams.get('id')} />);
}
