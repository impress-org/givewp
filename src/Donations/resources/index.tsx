import {StrictMode} from 'react';
import {createRoot} from 'react-dom/client';
import DonationsListTable from './components/DonationsListTable';

const root = createRoot(document.getElementById('give-admin-donations-root'));

root.render(
    <StrictMode>
        <DonationsListTable />
    </StrictMode>
);
