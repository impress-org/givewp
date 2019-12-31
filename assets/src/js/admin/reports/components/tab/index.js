import { Link, useRouteMatch } from 'react-router-dom'
import PropTypes from 'prop-types'

const Tab = ({to, children}) => {

    const match = useRouteMatch({
        path: to,
        exact: true
    })

    const classList = match ? 'nav-tab nav-tab-active' : 'nav-tab'

    return (
        <Link to={to} className={classList}>{children}</Link>
    )

}

export default Tab