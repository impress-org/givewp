import isRouteInlineRedirect from "@givewp/forms/app/utilities/isRouteInlineRedirect";

export default async function handleRedirect(url: string, inlineRedirectRoutes: string[]) {
    const redirectUrl = new URL(url);
    const redirectUrlParams = new URLSearchParams(redirectUrl.search);
    const shouldRedirectInline = isRouteInlineRedirect(redirectUrlParams, inlineRedirectRoutes);

    if (shouldRedirectInline) {
        // redirect inside iframe
        window.location.assign(redirectUrl);
    } else {
        // redirect outside iframe
        window.top.location.assign(redirectUrl);
    }
}