import {StrictMode} from 'react';
import {createRoot} from 'react-dom/client';
import './colors.scss';
import AddForm from './components/Onboarding/Components/AddForm';

const appContainer = document.createElement('div');
const target = document.querySelector('.wp-header-end');
target.parentNode.insertBefore(appContainer, target);

createRoot(appContainer).render(
    <StrictMode>
        <AddForm />
    </StrictMode>
);
