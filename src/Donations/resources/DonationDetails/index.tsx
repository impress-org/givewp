import {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import DonationDetails from './app/DonationDetails';

/**
 *
 * @unreleased
 */
const rootElement = document.getElementById('give-admin-donation-details-root');

if (rootElement) {
    ReactDOM.render(<StrictMode>{<DonationDetails />}</StrictMode>, rootElement);
}
