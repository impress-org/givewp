import {execFileSync} from 'child_process';

/**
 * Runs a WP-CLI command in the wp-env site this checkout started and returns its last line of output.
 *
 * Some fixtures have no REST route: a legacy (v2) donation form is a post with protected meta, and
 * GiveWP's settings are one option nobody exposes. WP-CLI is what wp-env offers for those, and it
 * talks to the environment `npm run env:start` created here - not to whatever `WP_BASE_URL` points
 * at. A run against a different site has to set that site up by hand.
 */
export function wp(...args: string[]): string {
    const output = execFileSync('npx', ['wp-env', 'run', 'cli', 'wp', ...args], {
        encoding: 'utf8',
        stdio: ['ignore', 'pipe', 'inherit'],
    });

    // wp-env wraps the command in status lines of its own; the command's result is the last line that is not one.
    const lines = output.split('\n').filter((line) => line.trim() && !/^[✔✖ℹ]/.test(line.trim()));

    return lines.pop()?.trim() ?? '';
}

/**
 * Whether the plugin with this slug is active on the wp-env site.
 */
export function isPluginActive(slug: string): boolean {
    return wp('plugin', 'list', '--status=active', '--field=name').split(/\s+/).includes(slug);
}

/**
 * Whether the Event Tickets beta feature is turned on.
 */
export function isEventTicketsEnabled(): boolean {
    return wp('eval', 'echo (int) Give\\BetaFeatures\\Facades\\FeatureFlag::eventTickets();') === '1';
}

/**
 * Saves the site options `names` as they are now, and returns a function that puts them back:
 * the old value for an option that was set, and no option at all for one that was not.
 *
 * A test that changes site options calls this first and the returned function in its cleanup, so
 * a developer site keeps its own settings after a run.
 */
export function saveOptions(...names: string[]): () => void {
    const list = names.map((name) => `'${name}'`).join(', ');
    const saved = wp(
        'eval',
        `$saved = [];
        foreach ([${list}] as $name) {
            $value = get_option($name, null);
            $saved[$name] = $value === null ? ['exists' => false] : ['exists' => true, 'value' => $value];
        }
        echo base64_encode(serialize($saved));`
    );

    return () => {
        wp(
            'eval',
            `foreach (unserialize(base64_decode('${saved}')) as $name => $option) {
                $option['exists'] ? update_option($name, $option['value']) : delete_option($name);
            }`
        );
    };
}

/**
 * Saves user `userId`'s meta under `key` as it is now, and returns a function that puts it back:
 * every old value, or no meta at all when there was none.
 */
export function saveUserMeta(userId: number, key: string): () => void {
    const saved = wp(
        'eval',
        `echo base64_encode(serialize(metadata_exists('user', ${userId}, '${key}')
            ? get_user_meta(${userId}, '${key}', false)
            : null));`
    );

    return () => {
        wp(
            'eval',
            `$values = unserialize(base64_decode('${saved}'));
            delete_user_meta(${userId}, '${key}');
            foreach ((array) $values as $value) {
                add_user_meta(${userId}, '${key}', $value);
            }`
        );
    };
}
