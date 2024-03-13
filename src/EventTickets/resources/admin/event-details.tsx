import {createRoot} from 'react-dom/client';
import EventDetailsPage from './components/EventDetailsPage';

const container = document.getElementById('give-admin-event-tickets-root');
const root = createRoot(container!);
root.render(<EventDetailsPage />);
