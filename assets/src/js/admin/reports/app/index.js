import { Switch, Route } from "react-router-dom"
import Tabs from '../components/tabs'


const App = (props) => {

    
    const routes = Object.values(giveReportsData.app.pages).map((page, index) => {
        console.log('route!', page);
        return(
            <Route exact path={page.path} key={index}>
                <h1>{page.title}</h1>
            </Route>
        )
    })

    return (
        <div className='wrap give-settings-page give-settings-setting-page give-settings-general-settings-section give-settings-general-tab'>
            <div className='give-settings-header'>
                <h1 className='wp-heading-inline'>Reports</h1>
            </div>
            <Tabs pages={giveReportsData.app.pages} />
            <div>
                <Switch>
                    {routes}
                </Switch>
            </div>
        </div>
    )
}
export default App