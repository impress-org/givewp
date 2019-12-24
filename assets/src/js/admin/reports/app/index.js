// Vendor dependencies
import { createContext } from 'react'
import { Switch, Route } from "react-router-dom"
import moment from 'moment'

// Store related dependencies
import { StoreProvider } from '../store'
import { reducer } from '../store/reducer'

// Components
import PeriodSelector from '../components/period-selector'
import Tabs from '../components/tabs'
import Routes from '../components/routes'

const App = (props) => {

    // Initial app state (available in component through useStoreValue)
    const initialState = {
        // Initial period range (defaults to the past week)
        period: { 
            startDate: moment().subtract(7, 'days'),
            endDate: moment(),
            range: 'week'
        }
    }

    return (
        <StoreProvider initialState={initialState} reducer={reducer}>
            <div className='wrap give-settings-page'>
                <div className='give-settings-header'>
                    <h1 className='wp-heading-inline'>Reports</h1>
                    <PeriodSelector />
                </div>
                <Tabs />
                <Routes />
            </div>
        </StoreProvider>
    )
}
export default App