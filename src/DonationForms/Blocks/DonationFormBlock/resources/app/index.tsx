import {createRoot, render} from '@wordpress/element';
import RevealForm from './Components/RevealForm';
import ModalForm from './Components/ModalForm';
import IframeResizer from 'iframe-resizer-react';

import './styles/index.scss';

/**
 * @since 3.0.0
 */
function DonationFormBlockApp({formFormat, dataSrc, embedId, openFormButton}) {
    if (formFormat === 'reveal') {
        return <RevealForm openFormButton={openFormButton} dataSrc={dataSrc} embedId={embedId} />;
    }

    if (formFormat === 'modal') {
        return <ModalForm openFormButton={openFormButton} dataSrc={dataSrc} embedId={embedId} />;
    }

    return (
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
    );
}

const roots = document.querySelectorAll('.root-data-givewp-embed');

roots.forEach((root) => {
    const dataSrc = root.getAttribute('data-src');
    const embedId = root.getAttribute('data-givewp-embed-id');
    const formFormat = root.getAttribute('data-form-format');
    const openFormButton = root.getAttribute('data-open-form-button');

    if (createRoot) {
        createRoot(root).render(
            <DonationFormBlockApp
                openFormButton={openFormButton}
                formFormat={formFormat}
                dataSrc={dataSrc}
                embedId={embedId}
            />
        );
    } else {
        render(
            <DonationFormBlockApp
                openFormButton={openFormButton}
                formFormat={formFormat}
                dataSrc={dataSrc}
                embedId={embedId}
            />,
            root
        );
    }
});
