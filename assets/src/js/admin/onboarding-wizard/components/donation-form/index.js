// Import vendor dependencies
import { useState, useEffect, useRef } from 'react';
import { __ } from '@wordpress/i18n'
import IframeResizer from 'iframe-resizer-react';

// Import utilities
import { getWindowData } from '../../utils';

// Import components
import ConfigurationIcon from '../icons/configuration';

// Import styles
import './style.scss';

const DonationForm = ({formId}) => {
    const formPreviewUrl = getWindowData('formPreviewUrl') + `${formId}`;
    const [isLoading, setLoading] = useState(false);
    const [previewHTML, setPreviewHTML] = useState(null);
    const iframeRef = useRef();

    useEffect(() => {
        setLoading(true);
    }, []);

    return (
        <div className="give-obw-donation-form-preview" data-givewp-test="preview-form">
            {isLoading && (
                <div className="give-obw-donation-form-preview__loading-message">
                    <ConfigurationIcon />
                    <h3>{__('Building Form Preview...', 'give')}</h3>
                </div>
            )}
            <IframeResizer
                id="donationFormPreview"
                className="give-obw-donation-form-preview__iframe"
                forwardRef={iframeRef}
                srcDoc={previewHTML}
                checkOrigin={
                    false
                } /** The srcDoc property is not a URL and requires that the origin check be disabled. */
                style={{
                    display: isLoading ? 'none' : 'inherit',
                    opacity: isLoading ? 0.5 : 1,
                }}
                onInit={(iframe) => {
                    iframe.iFrameResizer.resize();
                    setLoading(false);
                }}
            />

            {/* @note This iFrame is used to load and render the design preview document in the background. */}
            <iframe
                onLoad={(event) => {
                    const target = event.target;
                    setPreviewHTML(target.contentWindow.document.documentElement.innerHTML);
                }}
                src={formPreviewUrl}
                style={{display: 'none'}}
            />
        </div>
    );
};

export default DonationForm;
