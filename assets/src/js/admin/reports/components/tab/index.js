// Dependencies
import { Link, useRouteMatch } from 'react-router-dom';
import PropTypes from 'prop-types';

const Tab = ( { to, children } ) => {
	const match = useRouteMatch( {
		path: to,
		exact: true,
	} );

	const classList = match ? 'nav-tab nav-tab-active' : 'nav-tab';

	return (
		<Link to={ to } className={ classList }>{ children }</Link>
	);
};

Tab.propTypes = {
	// Route that is passed to react-router Link component
	to: PropTypes.string.isRequired,
	// Link children (typically text)
	children: PropTypes.node.isRequired,
};

Tab.defaultProps = {
	to: null,
	children: null,
};

export default Tab;
