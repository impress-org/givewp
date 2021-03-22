// Entry point for Donor Profile app

// Vendor dependencies
import { HashRouter as Router } from 'react-router-dom';
import ReactDOM from 'react-dom';
import React from 'react';
import { Provider } from 'react-redux';

import { library } from '@fortawesome/fontawesome-svg-core';
import { fas } from '@fortawesome/free-solid-svg-icons';

window.React = React;

library.add( fas );

// Store dependencies
import { store } from './store';

// Internal dependencies
import { registerDefaultTabs } from './tabs';
import { registerTab } from './utils';

// DonorDashboards app
import App from './components/app';

import './style.scss';

window.giveDonorDashboard = {
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
	document.getElementById( 'give-donor-dashboard' )
);

registerDefaultTabs();
