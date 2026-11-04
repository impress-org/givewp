import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import type {RecommendedProductData} from '@givewp/promotions/hooks/useRecommendations';
import type {ListTableNotice} from '../notices/types';
import ListTableNoticeArea from './index';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const recommendation: RecommendedProductData = {
    enum: 'givewp_donations_recurring_recommendation_dismissed',
    documentationPage: 'https://example.test/recurring',
    message: 'Accept monthly gifts.',
    innerHtml: 'Get Recurring Donations',
};

const notice = (id: string, kind: ListTableNotice['kind']): ListTableNotice => ({
    id,
    kind,
    title: `Title ${id}`,
    message: `Message ${id}`,
});

let container: HTMLDivElement;
let root: Root;

beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(() => root.unmount());
    container.remove();
});

const names = () =>
    Array.from(container.querySelectorAll('[role="group"]')).map(
        (group) =>
            group.getAttribute('aria-label') ??
            document.getElementById(group.getAttribute('aria-labelledby') ?? '')?.textContent
    );

describe('ListTableNoticeArea', () => {
    it('stacks system, onboarding, then the recommendation', async () => {
        await act(() =>
            root.render(
                <ListTableNoticeArea
                    notices={[notice('a', 'onboarding'), notice('b', 'system'), notice('c', 'onboarding')]}
                    recommendation={recommendation}
                    onDismissRecommendation={jest.fn()}
                />
            )
        );

        expect(names()).toEqual(['Title b', 'Title a', 'Title c', 'Pro tip']);
    });

    it('puts children (action errors) first and the deprecated slot last', async () => {
        await act(() =>
            root.render(
                <ListTableNoticeArea
                    notices={[notice('a', 'system')]}
                    recommendation={recommendation}
                    onDismissRecommendation={jest.fn()}
                    trailing={<p data-testid="old">old</p>}
                >
                    <p data-testid="errors">errors</p>
                </ListTableNoticeArea>
            )
        );

        const order = Array.from(container.querySelectorAll('[data-testid], [role="group"]')).map(
            (el) => el.getAttribute('data-testid') ?? 'group'
        );
        expect(order).toEqual(['errors', 'group', 'group', 'old']);
    });

    it('renders nothing when there is nothing to show', async () => {
        await act(() =>
            root.render(<ListTableNoticeArea notices={[]} recommendation={null} onDismissRecommendation={jest.fn()} />)
        );

        expect(container.innerHTML).toBe('');
    });

    it('names the recommendation and says its link opens a new tab', async () => {
        await act(() =>
            root.render(
                <ListTableNoticeArea notices={[]} recommendation={recommendation} onDismissRecommendation={jest.fn()} />
            )
        );

        expect(container.textContent).toContain('PRO TIP:');
        expect(container.textContent).toContain('Accept monthly gifts.');
        const link = container.querySelector('a') as HTMLAnchorElement;
        expect(link.getAttribute('href')).toBe('https://example.test/recurring');
        expect(link.target).toBe('_blank');
        expect(link.rel).toContain('noopener');
        expect(link.textContent).toContain('Get Recurring Donations');
        expect(link.querySelector('.screen-reader-text')?.textContent).toBe('(opens in a new tab)');
    });

    it('calls onDismissRecommendation from a labeled button', async () => {
        const onDismiss = jest.fn();
        await act(() =>
            root.render(
                <ListTableNoticeArea notices={[]} recommendation={recommendation} onDismissRecommendation={onDismiss} />
            )
        );

        const button = container.querySelector('button') as HTMLButtonElement;
        expect(button.getAttribute('aria-label')).toBe('Dismiss tip');
        await act(() => button.click());

        expect(onDismiss).toHaveBeenCalledTimes(1);
    });

    it('shows a notice link and calls its onDismiss', async () => {
        const onDismiss = jest.fn();
        await act(() =>
            root.render(
                <ListTableNoticeArea
                    notices={[
                        {
                            ...notice('a', 'system'),
                            link: {href: 'https://example.test/a', label: 'Read the guide', opensInNewTab: true},
                            onDismiss,
                        },
                    ]}
                    recommendation={null}
                    onDismissRecommendation={jest.fn()}
                />
            )
        );

        const link = container.querySelector('a') as HTMLAnchorElement;
        expect(link.textContent).toContain('Read the guide');
        expect(link.querySelector('.screen-reader-text')?.textContent).toBe('(opens in a new tab)');

        await act(() => (container.querySelector('button') as HTMLButtonElement).click());
        expect(onDismiss).toHaveBeenCalledTimes(1);
    });

    it('has no dismiss button on a notice without onDismiss', async () => {
        await act(() =>
            root.render(
                <ListTableNoticeArea
                    notices={[notice('a', 'system')]}
                    recommendation={null}
                    onDismissRecommendation={jest.fn()}
                />
            )
        );

        expect(container.querySelector('button')).toBeNull();
    });
});
