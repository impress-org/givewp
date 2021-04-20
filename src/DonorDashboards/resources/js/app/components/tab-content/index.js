import { useLocation } from 'react-router-dom';
import { useSelector } from 'react-redux';
import { Fragment } from 'react';
import Heading from '../heading';
import { __ } from '@wordpress/i18n';

import './style.scss';

const TabContent = () => {
	const location = useLocation();
	const tabsSelector = useSelector( state => state.tabs );
	const applicationError = useSelector( state => state.applicationError );

	const slug = location.pathname.length > 2 ? location.pathname.split( '/' )[ 1 ] : 'dashboard';
	const Content = tabsSelector[ slug ] ? tabsSelector[ slug ].content : null;

	return (
		<div className="give-donor-dashboard-tab-content">
			{ applicationError ? (
				<Fragment>
					<Heading icon="exclamation-triangle">
						{ __( 'Error', 'give' ) }
					</Heading>
					<p style={ { color: '#6b6b6b' } }>
						{ applicationError  }
					</p>
				</Fragment>
			): null }
			{ Content && applicationError === null ? <Content /> : null }
		</div>
	);
};
export default TabContent;
