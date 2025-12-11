import {createRoot} from 'react-dom/client';
import OrphanedFormsListTable from './app';

const container = document.getElementById('give_orphaned_forms_app');

if (container) {
    const root = createRoot(container);
    root.render(<OrphanedFormsListTable />);
}
