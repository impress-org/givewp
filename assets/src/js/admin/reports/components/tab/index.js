import { Link, useRouteMatch } from 'react-router-dom'

const Tab = (props) => {
    const match = useRouteMatch({
        path: props.to,
        exact: true
    })
    console.log('match!', match);
    const classList = match ? 'nav-tab nav-tab-active' : 'nav-tab'
    return (
        <Link to={props.to} exact={props.exact} className={classList}>{props.children}</Link>
    )
}
export default Tab