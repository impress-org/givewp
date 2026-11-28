import {readFileSync} from 'fs';

// The `@wordpress/dataviews` version the polyfill in this folder was last checked against.
const REVIEWED_VERSION = '10.1.7';

describe('DataViews polyfill', () => {
    it('was reviewed against the pinned DataViews version', () => {
        // When this fails the `@wordpress/dataviews` pin moved. Go through every entry in README.md in this
        // folder (remove it or check it), then set REVIEWED_VERSION to the new version.
        const {dependencies, devDependencies} = JSON.parse(readFileSync('package.json', 'utf8'));

        expect({...devDependencies, ...dependencies}['@wordpress/dataviews']).toBe(REVIEWED_VERSION);
    });
});
