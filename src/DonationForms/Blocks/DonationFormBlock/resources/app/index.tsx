import {createRoot} from '@wordpress/element';
import {__} from '@wordpress/i18n';
import IframeResizer from 'iframe-resizer-react';
import ModalForm from './Components/ModalForm';
import isRouteInlineRedirect from '@givewp/forms/app/utilities/isRouteInlineRedirect';

import '../editor/styles/index.scss';

/**
 * @since 3.2.1 Revert the display style value of "fullForm" to "onpage".
 * @since 3.1.2
 */
type DonationFormBlockAppProps = {
    formFormat: 'onpage' | 'newTab' | 'modal' | string;
    dataSrc: string;
    embedId: string;
    openFormButton: string;
    formUrl: string;
    formViewUrl: string;
};

/**
 * @since 3.4.0
 */
const inlineRedirectRoutes = ['donation-confirmation-receipt-view'];

/**
 * @since 3.4.0
 */
const isRedirect = (url: string) => {
    const redirectUrl = new URL(url);
    const redirectUrlParams = new URLSearchParams(redirectUrl.search);

    return isRouteInlineRedirect(redirectUrlParams, inlineRedirectRoutes);
};

/**
 * @since 3.4.0 add logic for inline redirects.
 * @since 3.2.0 replace form format reveal with new tab.
 * @since 3.0.0
 */
function DonationFormBlockApp({
    formFormat,
    dataSrc,
    embedId,
    openFormButton,
    formUrl,
    formViewUrl,
}: DonationFormBlockAppProps) {
    const isFormRedirect = isRedirect(dataSrc);

    if (formFormat === 'newTab') {
        return (
            <a className={'givewp-donation-form-link'} href={formUrl} target={'_blank'} rel={'noopener noreferrer'}>
                {openFormButton}
            </a>
        );
    }

    if (formFormat === 'modal' || formFormat === 'reveal') {
        return (
            <ModalForm
                openFormButton={openFormButton}
                dataSrc={dataSrc}
                embedId={embedId}
                isFormRedirect={isFormRedirect}
                formViewUrl={formViewUrl}
            />
        );
    }

    return (
        <IframeResizer
            title={__('Donation Form', 'give')}
            id={embedId}
            src={dataSrc}
            checkOrigin={false}
            heightCalculationMethod={'taggedElement'}
            style={{
                width: '1px',
                minWidth: '100%',
                border: '0',
            }}
        />
    );
}

const roots = document.querySelectorAll('.root-data-givewp-embed');

/**
 * @unreleased Add locale support
 */
roots.forEach((root) => {
    let dataSrcUrl = root.getAttribute('data-src');
    const locale = root.getAttribute('data-form-locale');
    if (locale) {
        const url = new URL(dataSrcUrl);
        url.searchParams.set('locale', locale);
        dataSrcUrl = url.toString();
    }

    const dataSrc = dataSrcUrl;
    const embedId = root.getAttribute('data-givewp-embed-id');
    const formFormat = root.getAttribute('data-form-format');
    const openFormButton = root.getAttribute('data-open-form-button');
    const formUrl = root.getAttribute('data-form-url');
    const formViewUrl = root.getAttribute('data-form-view-url');

    createRoot(root).render(
        <DonationFormBlockApp
            openFormButton={openFormButton}
            formFormat={formFormat}
            dataSrc={dataSrc}
            embedId={embedId}
            formUrl={formUrl}
            formViewUrl={formViewUrl}
        />
    );
});
