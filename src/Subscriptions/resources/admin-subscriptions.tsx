import {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import {createRoot} from 'react-dom/client';
import SubscriptionsListTable from './components/SubscriptionsListTable';

const root = document.getElementById('give-admin-subscriptions-root');

if (createRoot) {
    createRoot(root).render(
        <StrictMode>
            <SubscriptionsListTable />
        </StrictMode>
    );
}
else {
    ReactDOM.render(
        <StrictMode>
            <SubscriptionsListTable />
        </StrictMode>,
        root
    );
}
