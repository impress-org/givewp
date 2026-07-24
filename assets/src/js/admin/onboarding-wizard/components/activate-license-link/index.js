// Import vendor dependencies
import { __ } from '@wordpress/i18n';

// Import utilities
import { getWindowData } from '../../utils';

// Import styles
import './style.scss';

/**
 * Sends the user to the Liquid Web portal to activate their license, returning
 * them to the setup page afterwards. Renders nothing when there is no URL to
 * offer: the loaded Harbor predates the activation API, or the site is already
 * activated. The URL is prepared server-side in the wizard's localized data.
 */
const ActivateLicenseLink = () => {
	const activationUrl = getWindowData( 'activationUrl' );

	if ( ! activationUrl ) {
		return null;
	}

	return (
		<a
			className="give-obw-activate-license-link"
			href={ activationUrl }
			data-givewp-test="activate-license-link"
		>
			{ __( 'Activate your license', 'give' ) }
		</a>
	);
};

export default ActivateLicenseLink;
