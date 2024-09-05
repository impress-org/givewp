import {createRoot} from 'react-dom/client';
import CampaignsDetailsPage from './components/CampaignDetailsPage';

const container = document.getElementById('give-admin-campaigns-root');
const root = createRoot(container!);
root.render(<CampaignsDetailsPage />);
