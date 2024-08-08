import {StrictMode} from 'react';
import {createRoot} from 'react-dom/client';
import EditForm from './components/Onboarding/Components/EditForm';
import './colors.scss';

const root = createRoot(document.getElementById('give-admin-edit-v2form'));

root.render(
    <StrictMode>
        <EditForm />
    </StrictMode>
);
