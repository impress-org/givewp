import {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import './admin-donation-forms.module.scss';
import DonationFormsListTable from "./components/DonationFormsListTable";

ReactDOM.render(
    <StrictMode>
        <DonationFormsListTable />
    </StrictMode>,
    document.getElementById('give-admin-donation-forms-root')
);
