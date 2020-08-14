/* eslint-disable no-unused-vars */

// Entry point for Onboarind Wizard app

// Vendor dependencies
import React from 'react';
import ReactDOM from 'react-dom';

// Onboarding Wizard app
import App from './app/index.js';

// Import styles
import './style.scss';

// Render application
ReactDOM.render(
	<App />,
	document.getElementById( 'onboarding-wizard-app' )
);
