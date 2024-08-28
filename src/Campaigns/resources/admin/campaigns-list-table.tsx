import {createRoot} from 'react-dom/client';

const container = document.getElementById('give-admin-campaigns-root');
const root = createRoot(container!);
root.render(
    <div style={{padding: '200px 30px'}}>
        <h2>React APP</h2>
        <p>The campaigns list table will be loaded here...</p>
    </div>
);
