import { Switch, Route } from 'react-router-dom'
import OverviewPage from '../../pages/overview-page'

const Routes = () => {
    return (
        <Switch>
            <Route exact path='/'>
                <OverviewPage />
            </Route>
        </Switch>
    )
}
export default Routes