import {createRoot} from 'react-dom/client';

const container = document.getElementById('give-admin-campaigns-root');
const root = createRoot(container!);
root.render(
    <div style={{marginTop: '6rem', padding: '1rem'}}>
        <p>
            <strong>Campaign details goes here...</strong>
        </p>
    </div>
);
