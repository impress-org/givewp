// Handles display of different page components depending on location path
// Page components are found in app/pages

import { Switch, Route } from 'react-router-dom';
import OverviewPage from '../../app/pages/overview-page';

const Routes = () => {
	return (
		<Switch>
			<Route exact path="/">
				<OverviewPage />
			</Route>
		</Switch>
	);
};
export default Routes;
