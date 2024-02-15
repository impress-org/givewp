import {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import EventTicketsListTable from './components/EventTicketsListTable';

ReactDOM.render(
    <StrictMode>
        <EventTicketsListTable />
    </StrictMode>,
    document.getElementById('give-admin-event-tickets-root')
);
