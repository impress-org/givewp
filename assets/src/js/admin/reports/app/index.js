import {
    Switch,
    Route,
    Link
} from "react-router-dom";

const App = (props) => {

    const pages = giveReportsData.app.pages;
    const links = Object.values(pages).map((page, index) => {
        console.log('link!', page);
        return (
            <Link to={page.path} key={index}>{page.title}</Link>
        )
    })
    const routes = Object.values(pages).map((page, index) => {
        console.log('route!', page);
        return(
            <Route exact path={page.path} key={index}>
                <h1>{page.title}</h1>
            </Route>
        )
    })

    return (
        <div className="wrap">
            <h1 className="wp-heading-inline">Reports</h1>
            <hr className="wp-header-end"></hr>
            <div>
                <nav>
                    {links}
                </nav>
                <div>
                    <Switch>
                        {routes}
                    </Switch>
                </div>
            </div>
        </div>
    )
}
export default App