import {createRoot} from 'react-dom/client';

import Migrations from './Migrations';

const root = createRoot(document.getElementById('give_migrations_table_app'));
root.render(<Migrations />);
