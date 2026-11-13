import {wp} from './wp-cli';

/**
 * Test Mode and the Test Donation gateway (`manual`), which donation forms offer only while Test Mode
 * is enabled.
 *
 * GiveWP's settings are one option with no REST route, so these go through WP-CLI.
 */

export const TEST_GATEWAY = 'manual';

const OFFLINE_GATEWAY = 'offline';

const SETTINGS = ['test_mode', 'gateways', 'gateways_v3'];

/**
 * Enables Test Donation for both form generations, switches Test Mode off, and returns a function that
 * puts those settings back.
 *
 * Offline Donations is enabled beside it so each form still renders its gateway list, which is where
 * the assertions look for Test Donation. The other specs that complete a donation pay with Test
 * Donation, so a spec that calls this has to call the returned function once it is done.
 */
export function enableTestGatewayInLiveMode(): () => void {
    const saved = SETTINGS.map((key) => [key, wp('option', 'pluck', 'give_settings', key, '--format=json')]);

    for (const option of ['gateways', 'gateways_v3']) {
        const saved = JSON.parse(wp('option', 'pluck', 'give_settings', option, '--format=json') || '{}');

        // PHP encodes an empty array as `[]`, and keys added to a JavaScript array are dropped by JSON.stringify.
        const gateways = Array.isArray(saved) ? {} : saved;

        gateways[TEST_GATEWAY] = 1;
        gateways[OFFLINE_GATEWAY] = 1;

        wp('option', 'patch', 'update', 'give_settings', option, JSON.stringify(gateways), '--format=json');
    }

    wp('option', 'patch', 'update', 'give_settings', 'test_mode', 'disabled');

    return () => {
        for (const [key, value] of saved) {
            wp('option', 'patch', 'update', 'give_settings', key, value, '--format=json');
        }
    };
}
