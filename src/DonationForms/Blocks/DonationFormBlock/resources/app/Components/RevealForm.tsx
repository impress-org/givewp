import {useState} from '@wordpress/element';
import IframeResizer from 'iframe-resizer-react';

/**
 * @since 3.0.0
 */
export default function RevealForm({dataSrc, embedId, openFormButton}) {
    const [isRevealed, setIsRevealed] = useState(false);

    const revealForm = () => {
        setIsRevealed(!isRevealed);
    };

    return (
        <>
            <button className={'givewp-donation-form-display__button'} onClick={revealForm}>
                {openFormButton}
            </button>

            {isRevealed && (
                <IframeResizer
                    id={embedId}
                    src={dataSrc}
                    checkOrigin={false}
                    style={{
                        width: '1px',
                        minWidth: '100%',
                        border: '0',
                    }}
                />
            )}
        </>
    );
}
