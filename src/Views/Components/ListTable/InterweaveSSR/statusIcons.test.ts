import {readFileSync} from 'fs';
import {resolve} from 'path';

const src = (...path: string[]) => readFileSync(resolve(__dirname, '..', '..', '..', '..', ...path), 'utf8');
const styles = readFileSync(resolve(__dirname, 'styles.scss'), 'utf8').replace(/\/\/.*$/gm, '');

// Each status list column prints `statusBadge--{value}`. A value with no rule here shows the word with no icon.
// `give_subscription` is the renewal type, which the list shows as Completed, so it needs none.
describe('status icons', () => {
    it.each([
        ['Donations', 'Donations/ValueObjects/DonationStatus.php'],
        ['Campaigns', 'Campaigns/ValueObjects/CampaignStatus.php'],
        ['Forms', 'DonationForms/ValueObjects/DonationFormStatus.php'],
        ['Subscriptions', 'Subscriptions/ValueObjects/SubscriptionStatus.php'],
    ])('%s: every status value has an icon rule', (_screen, file) => {
        const values = [...src(file).matchAll(/^\s*const [A-Z_]+\s*=\s*'([a-z_]+)';/gm)].map((match) => match[1]);

        expect(values.length).toBeGreaterThan(0);
        expect(
            values.filter((value) => value !== 'give_subscription' && !new RegExp(`&--${value}\\b`).test(styles))
        ).toEqual([]);
    });
});

// The Goal cell is as quiet as the Status cell: the cell's own gray, no bold, and the same check.
describe('goal progress', () => {
    const goal = styles.slice(styles.indexOf('.givewp-goal-progress {'), styles.indexOf('.goalProgress {'));

    it('draws the achieved check with the Completed status icon, in the text color', () => {
        expect(styles.match(/\$icon-completed/g)?.length).toBeGreaterThanOrEqual(3);
        expect(goal).toContain('mask: $icon-completed center / contain no-repeat');
        expect(goal).toContain('background-color: currentColor');
    });

    it('sets no color or weight of its own on the text', () => {
        expect(goal).not.toMatch(/(?<![-\w])color:(?! CanvasText)/);
        expect(goal).not.toContain('font-weight');
    });
});
