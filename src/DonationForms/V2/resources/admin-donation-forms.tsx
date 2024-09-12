import {StrictMode} from 'react';
import {createRoot} from 'react-dom/client';
import DonationFormsListTable from './components/DonationFormsListTable';
import './colors.scss';

const root = document.getElementById('give-admin-donation-forms-root');

createRoot(root).render(
    <StrictMode>
        <DonationFormsListTable />
    </StrictMode>
);

