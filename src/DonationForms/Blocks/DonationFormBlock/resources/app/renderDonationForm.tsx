import { createRoot } from 'react-dom/client';
import DonationFormBlockApp from '.';

/**
 * @since 4.7.0
 */
export default function renderDonationForm(root) {
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
}
