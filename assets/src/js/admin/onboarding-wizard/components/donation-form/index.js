// Import vendor dependencies
import { useState } from 'react';
const { __ } = wp.i18n;

// Import utilities
import { getWindowData } from '../../utils';

// Import components
import ConfigurationIcon from '../icons/configuration';

// Import styles
import './style.scss';

const DonationForm = () => {
	const formPreviewUrl = getWindowData( 'formPreviewUrl' );
	const [ iframeLoaded, setIframeLoaded ] = useState( false );

	const iframeStyle = {
		opacity: iframeLoaded === false ? '0' : '1',
	};
	const messageStyle = {
		opacity: iframeLoaded === false ? '1' : '0',
	};
	return (
		<div className="give-obw-donation-form-preview">
			<div className="give-obw-donation-form-preview__loading-message" style={ messageStyle }>
				<ConfigurationIcon />
				<h3>
					{ __( 'Building Form Preview...', 'give' ) }
				</h3>
			</div>
			<iframe onLoad={ () => setIframeLoaded( true ) } className="give-obw-donation-form-preview__iframe" src={ formPreviewUrl } style={ iframeStyle } />
		</div>
	);
};

export default DonationForm;
