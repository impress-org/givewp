import {StrictMode} from 'react';
import {createRoot} from 'react-dom/client';
import ReactDOM from 'react-dom';
import DonationFormsListTable from './components/DonationFormsListTable';
import './colors.scss';

const root = document.getElementById('give-admin-donation-forms-root');

if (createRoot) {
    createRoot(root).render(
        <StrictMode>
            <DonationFormsListTable />
        </StrictMode>
    );
} else {
    ReactDOM.render(
        <StrictMode>
            <DonationFormsListTable />
        </StrictMode>,
        root
    );
}

