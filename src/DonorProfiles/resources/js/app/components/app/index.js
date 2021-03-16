import DesktopLayout from '../desktop-layout';
import MobileLayout from '../mobile-layout';
import Auth from '../auth';
import { useWindowSize, useAccentColor } from '../../hooks';
import { createGlobalStyle } from 'styled-components';
import { Fragment } from 'react';

const App = () => {
	const { width } = useWindowSize();
	const accentColor = useAccentColor();

	const GlobalStyles = createGlobalStyle`
	:root {
		--give-donor-dashboard-accent-color: ${ accentColor };
	}
	`;

	return (
		<Fragment>
			<GlobalStyles />
			<Auth>
				{ width < 920 ? (
					<MobileLayout />
				) : (
					<DesktopLayout />
				) }
			</Auth>
		</Fragment>
	);
};
export default App;
