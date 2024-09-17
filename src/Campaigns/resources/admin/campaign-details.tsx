//import {createRoot} from 'react-dom/client';
import {createRoot} from '@wordpress/element';
import CampaignsDetailsPage from './components/CampaignDetailsPage';

import '../store'

const container = document.getElementById('give-admin-campaigns-root');

if (container) {
    const root = createRoot(container);
    root.render(<CampaignsDetailsPage />);
}
