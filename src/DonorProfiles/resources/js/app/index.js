// Entry point for Donor Profile app

// Vendor dependencies
import { HashRouter as Router } from 'react-router-dom';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';

import { library } from '@fortawesome/fontawesome-svg-core';
import { fas } from '@fortawesome/free-solid-svg-icons';

library.add( fas );

// Store dependencies
import { store } from './store';

// Internal dependencies
import { registerDefaultTabs } from './tabs';
import { registerTab } from './utils';

// DonorProfiles app
import App from './components/app';

import './style.scss';

const donorDashboardContainer = document.getElementById( 'give-donor-profile' );

if ( donorDashboardContainer ) {
	window.giveDonorProfile = {
		store,
		utils: {
			registerTab,
		},
	};

	ReactDOM.render(
		<Provider store={ store }>
			<Router>
				<App />
			</Router>
		</Provider>,
		donorDashboardContainer
	);

	registerDefaultTabs();
}
