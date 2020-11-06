import { useLocation } from 'react-router-dom';
import { useSelector } from 'react-redux';

const TabContent = () => {
	const location = useLocation();
	const tabsSelector = useSelector( state => state.tabs );

	const slug = location.pathname.length > 2 ? location.pathname.substr( 1 ) : 'dashboard';
	const Content = tabsSelector[ slug ] ? tabsSelector[ slug ].content : null;

	return (
		<div>
			{ Content ? <Content /> : null }
		</div>
	);
};
export default TabContent;
