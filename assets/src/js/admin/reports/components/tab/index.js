import { Link, useRouteMatch } from 'react-router-dom'

const Tab = ({to, exact, children}) => {
    const match = useRouteMatch({
        path: to,
        exact: true
    })
    const classList = match ? 'nav-tab nav-tab-active' : 'nav-tab'
    return (
        <Link to={to} exact={exact} className={classList}>{children}</Link>
    )
}
export default Tab