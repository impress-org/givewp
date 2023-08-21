import {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import MigrationGuideBox from './components/Onboarding/Components/MigrationGuideBox';

console.log('MigrationGuideBox', MigrationGuideBox);

ReactDOM.render(
    <StrictMode>
        <MigrationGuideBox />
    </StrictMode>,
    document.getElementById('give-admin-edit-v2form-migration-guide-box')
);
