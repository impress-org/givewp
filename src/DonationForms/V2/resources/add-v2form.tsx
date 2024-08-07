import {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import {createRoot} from 'react-dom/client';
import AddForm from './components/Onboarding/Components/AddForm';
import './colors.scss';

const appContainer = document.createElement('div');
const target = document.querySelector('.wp-header-end');
target.parentNode.insertBefore(appContainer, target);

if (createRoot) {
    createRoot(appContainer).render(
        <StrictMode>
            <AddForm />
        </StrictMode>
    );
} else {
    ReactDOM.render(
        <StrictMode>
            <AddForm />
        </StrictMode>,
        appContainer
    );
}
