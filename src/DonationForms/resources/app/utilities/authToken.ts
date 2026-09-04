/**
 * The auth token the authentication route returns after a successful login.
 *
 * Inside a cross-site iframe the browser drops the WordPress login cookie, so
 * this token is what keeps the donor signed in for the validate and donate
 * requests. It is request transport, not form data: keeping it out of the form
 * values keeps it out of the client-side validation schema, which rejects keys
 * it does not know.
 *
 * It lives on window.givewp.form because the login template and the form app
 * ship in separate bundles, so module state would not be shared between them.
 *
 * @since TBD
 */
export function setAuthToken(token: string): void {
    window.givewp.form.authToken = token ?? '';
}

export function getAuthToken(): string {
    return window.givewp?.form?.authToken ?? '';
}
