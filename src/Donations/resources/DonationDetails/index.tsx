import {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import DonationDetails from './components/DonationDetails';

const rootElement = document.getElementById('give-admin-donation-details-root');

if (rootElement) {
    ReactDOM.render(<StrictMode>{<DonationDetails />}</StrictMode>, rootElement);
}
