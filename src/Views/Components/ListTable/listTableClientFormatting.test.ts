import {readdirSync, readFileSync} from 'fs';

describe('list table client code', () => {
    it('formats no money or dates in the browser', () => {
        const root = 'src/Views/Components/ListTable';
        const offenders = (readdirSync(root, {recursive: true}) as string[])
            .filter(
                (file) =>
                    /\.tsx?$/.test(file) && !/\.test\./.test(file) && !file.endsWith('filters/siteDate.ts')
            )
            .filter((file) =>
                /Intl\.|toLocaleString|navigator\.language|amountFormatter/.test(readFileSync(`${root}/${file}`, 'utf8'))
            );

        expect(offenders).toEqual([]);
    });
});
