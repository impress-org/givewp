import {__} from '@wordpress/i18n';
import isRouteInlineRedirect from '@givewp/forms/app/utilities/isRouteInlineRedirect';
import ModalForm from '@givewp/src/Campaigns/Blocks/shared/components/ModalForm';
import '../editor/styles/index.scss';
import renderDonationForm from './renderDonationForm';
import EmbedFrame from '@givewp/forms/shared/EmbedFrame';

/**
 * @since TBD add skeletonHtml, the server-rendered skeleton for the on-page loading state.
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
    skeletonHtml?: string;
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
 * @since TBD render every format through EmbedFrame for a shared loading and fallback state, with
 *            a skeleton of the form on the on-page format.
 * @since 4.3.0 replace ModalForm with Campaigns ModalForm.
 * @since 3.4.0 add logic for inline redirects.
 * @since 3.2.0 replace form format reveal with new tab.
 * @since 3.0.0
 */
export default function DonationFormBlockApp({
    formFormat,
    dataSrc,
    embedId,
    openFormButton,
    formUrl,
    formViewUrl,
    skeletonHtml,
}: DonationFormBlockAppProps) {
    const isFormRedirect = isRedirect(dataSrc);

    if (formFormat === 'newTab') {
        return (
            <a
                className={'givewp-donation-form-link'}
                href={formUrl}
                target={'_blank'}
                rel={'noopener noreferrer'}
                aria-label={`${openFormButton} ${__('Opens in a new tab', 'give')}`}
            >
                {openFormButton}
            </a>
        );
    }

    if (formFormat === 'modal' || formFormat === 'reveal') {
        return (
            <ModalForm
                buttonText={openFormButton}
                dataSrc={dataSrc}
                embedId={embedId}
                isFormRedirect={isFormRedirect}
                formViewUrl={formViewUrl}
                formUrl={formUrl}
            />
        );
    }

    return <EmbedFrame src={dataSrc} embedId={embedId} fallbackUrl={formUrl} skeletonHtml={skeletonHtml} />;
}

const roots = document.querySelectorAll('.root-data-givewp-embed');

/**
 * @since 4.7.0 update to use renderDonationForm
 * @since 3.22.0 Add locale support
 */
roots?.forEach((root) => {
    renderDonationForm(root);
});
