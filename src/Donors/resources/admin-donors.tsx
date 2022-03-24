import {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import './admin-donors.module.scss';
import DonorsListTable from "./components/DonorsListTable";

ReactDOM.render(
    <StrictMode>
        {<DonorsListTable />}
    </StrictMode>,
    document.getElementById('give-admin-donors-root')
);
