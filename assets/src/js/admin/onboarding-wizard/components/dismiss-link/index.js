// Import vendor dependencies
const { __ } = wp.i18n;

// Import utilities
import { getWindowData } from '../../utils';

// Import styles
import './style.scss';

const DismissLink = () => {
	const setupUrl = getWindowData( 'setupUrl' );
	return (
		<a className="give-obw-dismiss-link" href={ setupUrl }>
			{ __( 'Dismiss Setup Wizard', 'give' ) }
		</a>
	);
};

export default DismissLink;
