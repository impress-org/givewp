/**
 * Fired on the document when the inline login block signs the donor in.
 *
 * The login block and the email field are separate components in separate bundles, and the form
 * schema is fixed at render time, so the email field listens for this to lock itself without a reload.
 *
 * @since TBD
 */
export const DONOR_AUTHENTICATED_EVENT = 'givewp:donor-authenticated';
