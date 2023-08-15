import {useState} from '@wordpress/element';
import IframeResizer from 'iframe-resizer-react';

/**
 * @since 3.0.0
 */
export default function RevealPreview({enableIframe, formId, openFormButton}: any) {
    const [isRevealed, setIsRevealed] = useState(false);

    const revealForm = () => {
        setIsRevealed(!isRevealed);
    };

    return (
        <>
            <button onClick={revealForm} className={'givewp-form-block__display-button'}>
                {openFormButton}
            </button>

            {isRevealed && (
                <IframeResizer
                    src={`/?givewp-route=donation-form-view&form-id=${formId}`}
                    checkOrigin={false}
                    style={{
                        width: '1px',
                        minWidth: '100%',
                        border: '0',
                        pointerEvents: enableIframe,
                    }}
                />
            )}
        </>
    );
}
