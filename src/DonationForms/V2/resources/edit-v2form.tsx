import {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import EditForm from './components/Onboarding/Components/EditForm';
import './colors.scss';

ReactDOM.render(
    <StrictMode>
        <EditForm />
    </StrictMode>,
    document.getElementById('give-admin-edit-v2form')
);
