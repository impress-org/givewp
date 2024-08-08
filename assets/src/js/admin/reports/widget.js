// Entry point for dashboard widget

// Vendor dependencies
import {createRoot} from 'react-dom/client';
import moment from 'moment';

// Reports widget
import Widget from './widget/index.js';

import {StoreProvider} from './store';
import {reducer} from './store/reducer';

// Utils
import {getWindowData} from './utils';

const initialState = {
    // Initial period range (defaults to the past week)
    period: {
        startDate: moment().subtract(7, 'days'),
        endDate: moment(),
        range: 'week',
    },
    pageLoaded: false,
    giveStatus: null,
    currency: getWindowData('currency'),
    testMode: getWindowData('testMode'),
    assetsUrl: getWindowData('assetsUrl'),
};

const container = document.getElementById('givewp-reports-widget');

if (container) {
    createRoot(container).render(<StoreProvider initialState={initialState} reducer={reducer}>
            <Widget />
        </StoreProvider>
    );
}
