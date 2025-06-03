import {createRoot} from '@wordpress/element';
import DonorDetailsPage from './components/DonorDetailsPage';

const container = document.getElementById('give-admin-donors-root');
const urlParams = new URLSearchParams(window.location.search);
const donorId = parseInt(urlParams.get('id') ?? '0');

if (container) {
    const root = createRoot(container);
    root.render(<DonorDetailsPage donorId={donorId} />);
}
