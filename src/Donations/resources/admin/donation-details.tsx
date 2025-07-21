import {createRoot} from '@wordpress/element';
import DonationDetailsPage from './components/DonationDetailsPage';

const container = document.getElementById('give-admin-donations-root');

if (container) {
    const root = createRoot(container);
    root.render(<DonationDetailsPage />);
}
