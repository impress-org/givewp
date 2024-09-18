import {StrictMode} from 'react';
import {createRoot} from 'react-dom/client';
import AddForm from './components/Onboarding/Components/AddForm';
import './colors.scss';

const appContainer = document.createElement('div');
const target = document.querySelector('.wp-header-end');
target.parentNode.insertBefore(appContainer, target);

createRoot(appContainer).render(
    <StrictMode>
        <AddForm />
    </StrictMode>
);
