import {StrictMode} from 'react';
import {createRoot} from 'react-dom/client';
import ReactDOM from 'react-dom';
import EditForm from './components/Onboarding/Components/EditForm';
import './colors.scss';

const root = document.getElementById('give-admin-edit-v2form');

if (createRoot) {
    createRoot(root).render(
        <StrictMode>
            <EditForm />
        </StrictMode>
    );
} else {
    ReactDOM.render(
        <StrictMode>
            <EditForm />
        </StrictMode>,
        root
    );
}
