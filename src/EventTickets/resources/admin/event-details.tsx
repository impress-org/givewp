import {createRoot} from 'react-dom/client';

const container = document.getElementById('give-admin-event-tickets-root');
const root = createRoot(container!);
// @ts-ignore
root.render(<p>Event Details: {window.GiveEventTickets?.event?.title}</p>);
