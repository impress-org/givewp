import {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import AddForm from './components/Onboarding/Components/AddForm';
import './colors.scss';

const appContainer = document.createElement('div');
const target = document.querySelector('.wp-header-end');
target.parentNode.insertBefore(appContainer, target);

ReactDOM.render(
    <StrictMode>
        <AddForm />
    </StrictMode>,
    appContainer
);
