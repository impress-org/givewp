import {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import DonationFormsListTable from "./components/DonationFormsListTable";
import './colors.scss';

ReactDOM.render(
    <StrictMode>
        <DonationFormsListTable />
    </StrictMode>,
    document.getElementById('give-admin-donation-forms-root')
);
