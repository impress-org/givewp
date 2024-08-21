import {__} from '@wordpress/i18n';
import {Markup} from 'interweave';
import {Button} from '@wordpress/components';
import createIframePortal from './createIframePortal';

import './styles.scss';

export default function ConsentModal({setShowModal, modalHeading, modalAcceptanceText, agreementText, acceptTerms}) {
    return createIframePortal(
        <div className={'givewp-fields-consent-modal'} role="dialog" aria-label={modalHeading}>
            <div className={'givewp-fields-consent-modal-content'}>
                <h2>{modalHeading}</h2>

                <div className={'givewp-fields-consent-modal-content__agreement-text'}>
                    <Markup content={agreementText} />
                </div>

                <div className={'givewp-fields-consent-modal-content__actions'}>
                    <Button variant={'secondary'} onClick={() => setShowModal(false)}>
                        {__('Cancel', 'give')}
                    </Button>
                    <Button variant={'primary'} onClick={acceptTerms}>
                        {modalAcceptanceText}
                    </Button>
                </div>
            </div>
        </div>,
        window.top.document.body
    );
}
