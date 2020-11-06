// Entry point for Donor Profile app

// Vendor dependencies
import { HashRouter as Router } from 'react-router-dom';
import ReactDOM from 'react-dom';

// Reports app
import App from '../components/app';

ReactDOM.render(
	<Router>
		<App />
	</Router>,
	document.getElementById( 'give-donor-profile' )
);
