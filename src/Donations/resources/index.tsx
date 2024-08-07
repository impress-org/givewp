import {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import {createRoot} from 'react-dom/client';
import DonationsListTable from './components/DonationsListTable';

const root = document.getElementById('give-admin-donations-root');

if (createRoot) {
    createRoot(root).render(
        <StrictMode>
            <DonationsListTable />
        </StrictMode>
    );
} else {
    ReactDOM.render(
        <StrictMode>{<DonationsListTable />}</StrictMode>,
        root
    );
}
