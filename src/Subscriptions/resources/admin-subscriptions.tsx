import {StrictMode} from 'react';
import {createRoot} from 'react-dom/client';
import SubscriptionsListTable from './components/SubscriptionsListTable';

const element = document.getElementById('give-admin-subscriptions-root');

if (element) {
    createRoot(element).render(
        <StrictMode>
            <SubscriptionsListTable />
        </StrictMode>
    );
}
