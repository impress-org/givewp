// Entry point for Reports page app

// Vendor dependencies
import { HashRouter as Router } from 'react-router-dom';
import React from 'react';
import ReactDOM from 'react-dom';

// Reports app
import App from './app/index.js';

ReactDOM.render(
	<Router>
		<App />
	</Router>,
	document.getElementById( 'reports-app' )
);
