/**
 * @since 3.0.0
 */
export default function isRouteInlineRedirect(redirectUrlParams: URLSearchParams, routes: string[]): boolean {
    return redirectUrlParams.has('givewp-route') && routes.includes(redirectUrlParams.get('givewp-route'));
};