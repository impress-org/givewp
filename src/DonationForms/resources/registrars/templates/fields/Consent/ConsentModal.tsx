import {__} from '@wordpress/i18n';
import {createPortal} from 'react-dom';
import {Markup} from 'interweave';
import {Button} from '@wordpress/components';

export default function ConsentModal({setShowModal, modalHeading, modalAcceptanceText, agreementText, acceptTerms}) {
    const scrollModalIntoView = (element) => {
        if (element) {
            element.scrollIntoView({behavior: 'smooth', block: 'center', inline: 'nearest'});
        }
    };

    return createPortal(
        <div
            style={{
                position: 'absolute',
                top: 0,
                left: 0,
                bottom: 0,
                right: 0,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                background: 'transparent',
                backdropFilter: 'blur(2px)',
                zIndex: 999,
            }}
        >
            <div
                ref={(element) => {
                    scrollModalIntoView(element);
                }}
                style={{
                    background: 'var(--givep-shades-white, #fff)',
                    padding: '2.5rem 3.5rem',
                    width: 'calc(min(100%, 51.5rem) + 2rem)',
                    boxShadow: '0 0.25rem 0.5rem 0 rgba(230, 230, 230, 1)',
                    borderRadius: '.25rem',
                }}
            >
                <h2 style={{fontSize: '1.25rem', color: 'var(--givewp-primary-color)'}}>{modalHeading}</h2>

                <div style={{maxHeight: '24rem', marginBottom: '1.5rem', overflowY: 'scroll'}}>
                    <Markup content={agreementText} />
                </div>

                <div
                    style={{
                        display: 'flex',
                        gap: '1rem',
                    }}
                >
                    <Button
                        style={{
                            margin: 0,
                            background: 'transparent',
                            color: 'var(--givewp-primary-color)',
                            border: '1px solid var(--givewp-primary-color)',
                        }}
                        variant={'secondary'}
                        onClick={() => setShowModal(false)}
                    >
                        {__('Cancel', 'give')}
                    </Button>
                    <Button style={{margin: 0}} onClick={acceptTerms}>
                        {modalAcceptanceText}
                    </Button>
                </div>
            </div>
        </div>,
        document.body
    );
}
