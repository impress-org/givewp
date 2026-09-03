import {wp} from './wp-cli';

/**
 * Fixtures for the legacy (v2) donation form, which has no REST route and cannot be created in
 * wp-admin any more - the classic "Add New" screen redirects to campaigns.
 */

/**
 * Creates a published v2 form on the multi-step (Sequoia) template with one set amount.
 *
 * The template matters: the multi-step form embeds in an iframe and builds its success URL from the
 * page it is on, which is how a return URL ends up carrying a query string of its own.
 */
export function createLegacyForm(): {title: string; formId: number} {
    const title = `E2E legacy form ${Date.now()}`;

    const formId = Number(
        wp(
            'post',
            'create',
            '--post_type=give_forms',
            '--post_status=publish',
            `--post_title=${title}`,
            `--meta_input=${JSON.stringify({
                _give_form_template: 'sequoia',
                _give_price_option: 'set',
                _give_set_price: '10.00',
                // Without this the donor fields are not rendered at all; the editor saves it as 'none' by default.
                _give_show_register_form: 'none',
            })}`,
            '--porcelain'
        )
    );

    if (!formId) {
        throw new Error('WP-CLI did not return the id of the legacy form it was asked to create.');
    }

    return {title, formId};
}
