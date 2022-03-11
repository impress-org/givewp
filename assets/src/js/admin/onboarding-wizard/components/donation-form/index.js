// Import vendor dependencies
import { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n'

// Import utilities
import { getWindowData } from '../../utils';

// Import components
import ConfigurationIcon from '../icons/configuration';

// Import styles
import './style.scss';

const DonationForm = () => {
	const formPreviewUrl = getWindowData( 'formPreviewUrl' );
	const [ iframeLoaded, setIframeLoaded ] = useState( false );
	const [ iframeHeight, setIframeHeight ] = useState( 749 );

	useEffect( () => {
		window.addEventListener( 'message', receiveMessage, false );
		return () => {
			window.removeEventListener( 'message', receiveMessage, false );
		};
	}, [] );

	const receiveMessage = ( event ) => {
		switch ( event.data.action ) {
			case 'resize': {
				setIframeHeight( event.data.payload.height );
				break;
			}
			case 'loaded': {
				onIframeLoaded();
				break;
			}
			default: {

			}
		}
	};

	const iframeStyle = {
		height: iframeHeight,
		opacity: iframeLoaded === false ? '0' : '1',
	};
	const messageStyle = {
		height: iframeHeight,
		opacity: iframeLoaded === false ? '1' : '0',
	};

	const onIframeLoaded = () => {
		setIframeLoaded( true );
		hideInIframe( '#give_error_test_mode' );
		hideInIframe( '.social-sharing' );
	};

	const hideInIframe = ( selector ) => {
		const element = document.getElementById( 'donationFormPreview' ).contentDocument
			.getElementById( 'iFrameResizer0' ).contentDocument
			.querySelector( selector );
		if ( element ) {
			element.style.display = 'none';
		}
	};

	return (
		<div className="give-obw-donation-form-preview" data-givewp-test="preview-form">
			<div className="give-obw-donation-form-preview__loading-message" style={ messageStyle }>
				<ConfigurationIcon />
				<h3>
					{ __( 'Building Form Preview...', 'give' ) }
				</h3>
			</div>
			<iframe id="donationFormPreview" className="give-obw-donation-form-preview__iframe" scrolling="no" src={ formPreviewUrl } style={ iframeStyle } />
		</div>
	);
};

export default DonationForm;
