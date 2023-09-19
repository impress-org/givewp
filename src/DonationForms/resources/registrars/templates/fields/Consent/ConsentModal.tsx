import {__} from '@wordpress/i18n';
import {createPortal} from 'react-dom';
import {Markup} from 'interweave';
import {Button} from '@wordpress/components';

import './styles.scss';

export default function ConsentModal({setShowModal, modalHeading, modalAcceptanceText, agreementText, acceptTerms}) {
    const scrollModalIntoView = (element) => {
        if (element) {
            element.scrollIntoView({behavior: 'smooth', block: 'center', inline: 'nearest'});
        }
    };

    return createPortal(
        <div className={'givewp-fields-consent-modal'} role="dialog" aria-label={modalHeading}>
            <div
                className={'givewp-fields-consent-modal-content'}
                ref={(element) => {
                    element && scrollModalIntoView(element);
                }}
            >
                <h2>{modalHeading}</h2>

                <div className={'givewp-fields-consent-modal-content__agreement-text'}>
                    <Markup content={agreementText} />
                </div>

                <div className={'givewp-fields-consent-modal-content__actions'}>
                    <Button variant={'secondary'} onClick={() => setShowModal(false)}>
                        {__('Cancel', 'give')}
                    </Button>
                    <Button onClick={acceptTerms}>{modalAcceptanceText}</Button>
                </div>
            </div>
        </div>,
        document.body
    );
}
