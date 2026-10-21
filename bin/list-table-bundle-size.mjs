#!/usr/bin/env node
/**
 * Prints the gzip size of the JavaScript each admin list screen loads: its own entry plus the
 * shared list table chunk. Compare a branch with a baseline saved from `develop`.
 *
 * Usage (after `npm run build`):
 *   node bin/list-table-bundle-size.mjs --save=baseline.json   # on develop
 *   node bin/list-table-bundle-size.mjs --baseline=baseline.json  # on the branch
 *
 * @since TBD
 */
import {readFileSync, writeFileSync, existsSync} from 'node:fs';
import {gzipSync} from 'node:zlib';
import {resolve} from 'node:path';

const BUDGET_BYTES = 100 * 1024;
const SHARED_CHUNK = 'build/givewp-list-table.js';
const DATAVIEWS_MARKER = 'dataviews-view-table';

const SCREENS = {
    Campaigns: 'build/campaignListTable.js',
    'Campaign details': 'build/campaignDetails.js',
    'Donation Forms': 'build/assets/dist/js/give-admin-donation-forms.js',
    Donations: 'build/assets/dist/js/give-admin-donations.js',
    Donors: 'build/assets/dist/js/give-admin-donors.js',
    Subscriptions: 'build/assets/dist/js/give-admin-subscriptions.js',
    'Subscription details': 'build/subscriptionDetails.js',
    'Event Tickets': 'build/assets/dist/js/give-admin-event-tickets.js',
};

const args = Object.fromEntries(
    process.argv.slice(2).map((arg) => {
        const [key, value = 'true'] = arg.replace(/^--/, '').split('=');
        return [key, value];
    })
);

function gzipSize(relativePath) {
    const file = resolve(process.cwd(), relativePath);

    if (!existsSync(file)) {
        return null;
    }

    return gzipSync(readFileSync(file), {level: 9}).length;
}

function containsDataViews(relativePath) {
    const file = resolve(process.cwd(), relativePath);

    return existsSync(file) && readFileSync(file, 'utf8').includes(DATAVIEWS_MARKER);
}

const sharedSize = gzipSize(SHARED_CHUNK) ?? 0;
const baseline = args.baseline ? JSON.parse(readFileSync(args.baseline, 'utf8')) : null;

const rows = Object.entries(SCREENS).map(([screen, entry]) => {
    const own = gzipSize(entry);
    const total = own === null ? null : own + sharedSize;
    const before = baseline?.[screen]?.total ?? null;
    const delta = before === null || total === null ? null : total - before;

    return {
        screen,
        own,
        shared: sharedSize,
        total,
        before,
        delta,
        pass: delta === null ? null : delta <= BUDGET_BYTES,
        bundlesDataViewsItself: sharedSize > 0 && containsDataViews(entry),
    };
});

const kb = (bytes) => (bytes === null ? '—' : `${(bytes / 1024).toFixed(1)} KB`);

console.table(
    rows.map((row) => ({
        screen: row.screen,
        own: kb(row.own),
        shared: kb(row.shared),
        total: kb(row.total),
        before: kb(row.before),
        delta: kb(row.delta),
        pass: row.pass ?? '—',
        'DataViews in entry': row.bundlesDataViewsItself,
    }))
);

if (args.save) {
    writeFileSync(args.save, JSON.stringify(Object.fromEntries(rows.map((row) => [row.screen, row])), null, 2));
    console.log(`Saved to ${args.save}`);
}

const failed = rows.some((row) => row.pass === false || row.bundlesDataViewsItself);
process.exitCode = failed ? 1 : 0;
