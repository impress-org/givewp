import {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import DonationsListTable from './ListTable';

ReactDOM.render(
    <StrictMode>
        {<DonationsListTable/>}
    </StrictMode>,
    document.getElementById('give-admin-donations-root')
);
