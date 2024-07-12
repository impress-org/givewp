// Import vendor dependencies
import {useState, useEffect, useRef} from 'react';
import { __ } from '@wordpress/i18n'

// Import utilities
import { getWindowData } from '../../utils';

// Import components
import ConfigurationIcon from '../icons/configuration';

// Import styles
import './style.scss';

const DonationForm = ({formId}) => {
	const formPreviewUrl = getWindowData( 'formPreviewUrl' ) + `${formId}`;
	const [ iframeLoaded, setIframeLoaded ] = useState( false );
	const [ iframeHeight, setIframeHeight ] = useState( 749 );
    const iframeRef = useRef();

    useEffect( () => {
        const iframe = iframeRef.current
		iframe.addEventListener( 'load', onIframeLoaded, false );
		return () => {
			iframe.removeEventListener( 'load', onIframeLoaded, false );
		};
	}, [] );

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

        if (iframeRef.current?.contentWindow?.document) {
            setIframeHeight(iframeRef.current?.contentWindow?.document?.scrollHeight);
        }
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
			<iframe ref={iframeRef} id="donationFormPreview" className="give-obw-donation-form-preview__iframe" src={ formPreviewUrl } style={ iframeStyle } />
		</div>
	);
};

export default DonationForm;
