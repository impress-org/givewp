import {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import SubscriptionsListTable from './components/SubscriptionsListTable';

ReactDOM.render(
    <StrictMode>
        <SubscriptionsListTable />
    </StrictMode>,
    document.getElementById('give-admin-subscriptions-root')
);
