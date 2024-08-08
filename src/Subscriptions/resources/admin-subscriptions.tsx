import {StrictMode} from 'react';
import {createRoot} from 'react-dom/client';
import SubscriptionsListTable from './components/SubscriptionsListTable';

const root = createRoot(document.getElementById('give-admin-subscriptions-root'));

root.render(
    <StrictMode>
        <SubscriptionsListTable />
    </StrictMode>
);
