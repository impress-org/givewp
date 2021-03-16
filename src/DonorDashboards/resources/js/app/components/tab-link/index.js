import { NavLink } from 'react-router-dom';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import './style.scss';

const TabLink = ( { slug, label, icon } ) => {
	return (
		<NavLink to={ `/${ slug }` } className="give-donor-dashboard-tab-link" activeClassName="give-donor-dashboard-tab-link--is-active">
			<FontAwesomeIcon icon={ icon } fixedWidth={ true } />
			{ label }
		</NavLink>
	);
};
export default TabLink;
