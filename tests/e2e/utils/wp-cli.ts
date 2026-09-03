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
