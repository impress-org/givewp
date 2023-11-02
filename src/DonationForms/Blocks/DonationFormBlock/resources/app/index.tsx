import {createRoot, render} from '@wordpress/element';
import ModalForm from './Components/ModalForm';
import IframeResizer from 'iframe-resizer-react';

import '../editor/styles/index.scss';

type DonationFormBlockAppProps = {
    formFormat: 'full' | 'link' | 'modal' | string;
    dataSrc: string;
    embedId: string;
    openFormButton: string;
    permalink: string;
};

/**
 * @unreleased replace display style reveal with new tab link.
 * @since 3.0.0
 */
function DonationFormBlockApp({formFormat, dataSrc, embedId, openFormButton, permalink}: DonationFormBlockAppProps) {
    if (formFormat === 'link') {
        return (
            <a className={'givewp-donation-form-link'} href={permalink} target={'_blank'} rel={'noopener noreferrer'}>
                {openFormButton}
            </a>
        );
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
    const permalink = root.getAttribute('data-permalink');

    if (createRoot) {
        createRoot(root).render(
            <DonationFormBlockApp
                openFormButton={openFormButton}
                formFormat={formFormat}
                dataSrc={dataSrc}
                embedId={embedId}
                permalink={permalink}
            />
        );
    } else {
        render(
            <DonationFormBlockApp
                openFormButton={openFormButton}
                formFormat={formFormat}
                dataSrc={dataSrc}
                embedId={embedId}
                permalink={permalink}
            />,
            root
        );
    }
});
