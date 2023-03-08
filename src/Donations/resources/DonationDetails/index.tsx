import {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import App from './app/app';

/**
 *
 * @unreleased
 */
const rootElement = document.getElementById('give-admin-donation-details-root');

if (rootElement) {
    ReactDOM.render(<StrictMode>{<App />}</StrictMode>, rootElement);
}
