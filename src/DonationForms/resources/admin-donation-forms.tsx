import {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import ListFormsPage from './components/ListFormsPage';
import './admin-donation-forms.module.scss';

ReactDOM.render(
    <StrictMode>
        <ListFormsPage />
    </StrictMode>,
    document.getElementById('give-admin-donation-forms-root')
);
