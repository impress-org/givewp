import {createRoot} from 'react-dom/client';
import {AddonsAdminPage} from './components/AddonsAdminPage';

const searchParams = new URLSearchParams(window.location.search);
const startingTab = parseInt(searchParams.get('tab')) || 0;

const container = document.getElementById(window.GiveAddons.containerId);

if (container) {
    const root = createRoot(container);
    root.render(<AddonsAdminPage startingTab={startingTab} />);
}
