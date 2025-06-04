import {createRoot} from '@wordpress/element';
import DonorDetailsPage from './components/DonorDetailsPage';

const container = document.getElementById('give-admin-donors-root');

if (container) {
    const root = createRoot(container);
    root.render(<DonorDetailsPage />);
}
