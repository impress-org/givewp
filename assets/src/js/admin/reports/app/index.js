// Reports page app

// Vendor dependencies
import moment from 'moment';
import {__} from '@wordpress/i18n';

import './style.scss';

// Store related dependencies
import {StoreProvider} from '../store';
import {reducer} from '../store/reducer';

// Utils
import {getWindowData} from '../utils';

// Components
import PeriodSelector from '../components/period-selector';
import SettingsToggle from '../components/settings-toggle';
import Tabs from '../components/tabs';
import Routes from '../components/routes';
import ProductRecommendation from "../components/ProductRecommendation";

/**
 * @since 2.27.1
 */
const App = () => {
    // Initial app state (available in component through useStoreValue)
    const initialState = {
        // Initial period range (defaults to the past week)
        period: {
            startDate: moment().hour(0).subtract(7, 'days'),
            endDate: moment().hour(23),
            range: 'week',
        },
        // giveStatus: null
        pageLoaded: false,
        settingsPanelToggled: false,
        currency: getWindowData('currency'),
        testMode: getWindowData('testMode'),
    };


    return (
        <StoreProvider initialState={initialState} reducer={reducer}>
            <div className="wrap give-settings-page" style={{position: 'relative'}}>
                <div className="give-settings-header">
                    <h1 className="wp-heading-inline">{__('Reports', 'give')}</h1>
                    <div className="givewp-filters">
                        <PeriodSelector />
                        <SettingsToggle />
                    </div>
                </div>
                <hr className="wp-header-end hidden" />
                <Tabs />
                <ProductRecommendation />
                <br />
                <Routes />
            </div>
        </StoreProvider>
    );
};
export default App;
