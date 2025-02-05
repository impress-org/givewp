import isRouteInlineRedirect from '@givewp/forms/app/utilities/isRouteInlineRedirect';
import getCurrentFormUrlData from '@givewp/forms/app/utilities/getCurrentFormUrlData';

export default async function handleRedirect(url: string, inlineRedirectRoutes: string[]) {
    const redirectUrl = new URL(url);
    const redirectUrlParams = new URLSearchParams(redirectUrl.search);
    const shouldRedirectInline = isRouteInlineRedirect(redirectUrlParams, inlineRedirectRoutes);

    const {locale} = getCurrentFormUrlData();

    if (locale) {
        redirectUrl.searchParams.set('locale', locale);
    }

    if (shouldRedirectInline) {
        // redirect inside iframe
        window.location.assign(redirectUrl);
    } else {
        // redirect outside iframe
        window.top.location.assign(redirectUrl);
    }
}
