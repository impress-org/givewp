import {createRoot} from 'react-dom/client';
import EventTicketsListTable from './components/EventTicketsListTable';

const container = document.getElementById('give-admin-event-tickets-root');
const root = createRoot(container!);
root.render(<EventTicketsListTable />);
