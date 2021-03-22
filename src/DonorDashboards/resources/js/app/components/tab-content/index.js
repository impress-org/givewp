import { useLocation } from 'react-router-dom';
import { useSelector } from 'react-redux';

import './style.scss';

const TabContent = () => {
	const location = useLocation();
	const tabsSelector = useSelector( state => state.tabs );

	const slug = location.pathname.length > 2 ? location.pathname.split( '/' )[ 1 ] : 'dashboard';
	const Content = tabsSelector[ slug ] ? tabsSelector[ slug ].content : null;

	return (
		<div className="give-donor-dashboard-tab-content">
			{ Content ? <Content /> : null }
		</div>
	);
};
export default TabContent;
