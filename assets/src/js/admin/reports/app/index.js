import { createContext } from 'react'
import moment from 'moment'
import { Switch, Route } from "react-router-dom"
import PeriodSelector from '../components/period-selector'
import Tabs from '../components/tabs'
import Routes from '../components/routes'
import { StoreProvider } from '../store'
import { reducer } from '../store/reducer'

const App = (props) => {

    const initialState = {
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