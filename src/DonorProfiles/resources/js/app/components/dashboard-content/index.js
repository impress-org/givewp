import { useState, useEffect } from 'react';
import { useSelector } from 'react-redux';

const DashboardContent = () => {
	const tabsSelector = useSelector( state => state.tabs );
	const [ tabs, setTabs ] = useState( [] );
	const [ dashboardContent, setDashboardContent ] = useState( [] );

	useEffect( () => {
		setTabs( Object.entries( tabsSelector ) );
	}, [ tabsSelector ] );

	useEffect( () => {
		if ( tabs.length > 2 ) {
			setDashboardContent( tabs.reduce( ( content, tab ) => {
				if ( tab[ 1 ].dashboardContent ) {
					const Content = tab[ 1 ].dashboardContent;
					content.push( <Content /> );
				}
				return content;
			}, [] ) );
		}
	}, [ tabs ] );

	return (
		<div className="give-donor-profile-dashboard-content">
			{ dashboardContent }
		</div>
	);
};
export default DashboardContent;
