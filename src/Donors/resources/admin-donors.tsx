import { StrictMode } from '@wordpress/element';
import {createRoot} from 'react-dom/client';
import DonorsListTable from './components/DonorsListTable';

const root = createRoot(document.getElementById('give-admin-donors-root'));

root.render(
    <StrictMode>
        <DonorsListTable />
    </StrictMode>,
);
