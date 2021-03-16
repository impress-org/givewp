import { useState, useEffect } from 'react';
import { useSelector } from 'react-redux';

import './style.scss';

const DashboardContent = () => {
	const tabsSelector = useSelector( state => state.tabs );
	const [ tabs, setTabs ] = useState( [] );
	const [ dashboardContent, setDashboardContent ] = useState( [] );

	useEffect( () => {
		setTabs( Object.entries( tabsSelector ) );
	}, [ tabsSelector ] );

	useEffect( () => {
		if ( tabs.length > 2 ) {
			setDashboardContent( getDashboardContent( tabs ) );
		}
	}, [ tabs ] );

	const getDashboardContent = ( tabsArray ) => {
		return tabsArray.reduce( ( content, tab, index ) => {
			if ( tab[ 1 ].dashboardContent ) {
				const Content = tab[ 1 ].dashboardContent;
				content.push( <Content key={ index } /> );
			}
			return content;
		}, [] );
	};

	return (
		<div className="give-donor-dashboard-dashboard-content">
			{ dashboardContent }
		</div>
	);
};
export default DashboardContent;
