// Import vendor dependencies
import { __ } from '@wordpress/i18n'

// Import utilities
import { getWindowData } from '../../utils';

// Import styles
import './style.scss';

const DismissLink = () => {
	const setupUrl = getWindowData( 'setupUrl' );
	return (
		<a className="give-obw-dismiss-link" href={ setupUrl } data-givewp-test="dismiss-wizard-link">
			{ __( 'Dismiss Setup Wizard', 'give' ) }
		</a>
	);
};

export default DismissLink;
