// Store dependencies
import { useSelector } from 'react-redux';

// Internal dependencies
import TabLink from '../tab-link';

import './style.scss';

const TabMenu = () => {
	const tabsSelector = useSelector( state => state.tabs );
	const tabLinks = Object.values( tabsSelector ).map( ( tab, index ) => {
		return (
			<TabLink slug={ tab.slug } label={ tab.label } icon={ tab.icon } key={ index } />
		);
	} );

	return (
		<div className="give-donor-profile-tab-menu">
			{ tabLinks }
		</div>
	);
};
export default TabMenu;
