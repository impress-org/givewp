/**
 * @unreleased
 */
export default function isRouteInlineRedirect(redirectUrlParams: URLSearchParams, routes: string[]): boolean {
    return redirectUrlParams.has('givewp-route') && routes.includes(redirectUrlParams.get('givewp-route'));
};