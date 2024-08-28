import {createRoot} from 'react-dom/client';
import CampaignsListTable from './components/CampaignsListTable';

const container = document.getElementById('give-admin-campaigns-root');
const root = createRoot(container!);
root.render(<CampaignsListTable />);
