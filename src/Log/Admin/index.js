import {createRoot} from 'react-dom/client';

import Logs from './Logs';

const root = createRoot(document.getElementById('give-logs-list-table-app'));
root.render(<Logs />);
