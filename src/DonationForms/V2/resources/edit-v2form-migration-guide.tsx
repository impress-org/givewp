import {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import MigrationGuideBox from './components/Onboarding/Components/MigrationGuideBox';
import './colors.scss';

ReactDOM.render(
    <StrictMode>
        <MigrationGuideBox />
    </StrictMode>,
    document.getElementById('give-admin-edit-v2form-migration-guide-box')
);
