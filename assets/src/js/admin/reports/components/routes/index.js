import { Switch, Route } from 'react-router-dom'
import Page from '../page'

const Routes = (props) => {
    const routes = Object.values(props.pages).map((page, index) => {
        return(
            <Route exact path={page.path} key={index}>
                <Page page={page} />
            </Route>
        )
    })
    return (
        <Switch>
            {routes}
        </Switch>
    )
}
export default Routes