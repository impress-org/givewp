import {createRoot} from 'react-dom/client';
import DonationFormBlockApp from '.';
import type {SkeletonData} from '@givewp/forms/shared/EmbedFrame/EmbedSkeleton';

/**
 * The skeleton attribute is optional and best-effort: a host that strips or mangles it just gets the
 * spinner.
 *
 * @since TBD
 */
function readSkeletonData(root: Element): SkeletonData | null {
    try {
        return JSON.parse(root.getAttribute('data-form-skeleton') ?? 'null');
    } catch {
        return null;
    }
}

/**
 * @since TBD pass the skeleton data through for the loading state.
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
    const skeletonData = readSkeletonData(root);

    createRoot(root).render(
        <DonationFormBlockApp
            openFormButton={openFormButton}
            formFormat={formFormat}
            dataSrc={dataSrc}
            embedId={embedId}
            formUrl={formUrl}
            formViewUrl={formViewUrl}
            skeletonData={skeletonData}
        />
    );
}
