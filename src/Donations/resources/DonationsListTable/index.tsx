import {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import DonationsListTable from './app/DonationsListTable';

ReactDOM.render(
    <StrictMode>{<DonationsListTable />}</StrictMode>,
    document.getElementById('give-admin-donations-root')
);
