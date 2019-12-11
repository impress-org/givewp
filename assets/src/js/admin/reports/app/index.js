import { Switch, Route } from "react-router-dom"
import Tabs from '../components/tabs'
import Routes from '../components/routes'

const App = (props) => {
    return (
        <div className='wrap give-settings-page'>
            <div className='give-settings-header'>
                <h1 className='wp-heading-inline'>Reports</h1>
            </div>
            <Tabs pages={giveReportsData.app.pages} />
            <Routes pages={giveReportsData.app.pages} />
        </div>
    )
}
export default App