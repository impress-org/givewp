import {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import AdminDonationFormsPage from './components/AdminDonationFormsPage';
import './admin-donation-forms.module.scss';

ReactDOM.render(
    <StrictMode>
        <AdminDonationFormsPage />
    </StrictMode>,
    document.getElementById('give-admin-donation-forms-root')
);
