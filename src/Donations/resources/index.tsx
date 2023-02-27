import {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import DonationsListTable from './components/DonationsListTable';

ReactDOM.render(
    <StrictMode>{<DonationsListTable />}</StrictMode>,
    document.getElementById('give-admin-donations-root')
);
