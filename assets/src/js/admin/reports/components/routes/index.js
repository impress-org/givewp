import { Switch, Route } from 'react-router-dom'

const Routes = (props) => {
    
    return (
        <Switch>
            <Route exact path='/'>
                <h1>Overview Page</h1>
            </Route>
        </Switch>
    )
}
export default Routes