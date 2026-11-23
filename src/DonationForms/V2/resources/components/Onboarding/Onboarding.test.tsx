import {act} from 'react';
import {createRoot} from 'react-dom/client';
import ListTablePageHeader from '../../../../../Views/Components/ListTable/ListTablePage/ListTablePageHeader';
import Onboarding, {OnboardingContext} from './index';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

jest.mock('./Dialogs', () => ({FeatureNoticeDialog: () => <p>Feature notice</p>}));

// The list page header calls the banner as a function, inside the provider.
const renderHeader = async (showFeatureNoticeDialog: boolean) => {
    const container = document.createElement('div');
    const root = createRoot(container);

    await act(() =>
        root.render(
            <OnboardingContext.Provider value={[{showFeatureNoticeDialog}, jest.fn()]}>
                <ListTablePageHeader title="Forms" banner={Onboarding} testMode={false} onChangeTestMode={jest.fn()} />
            </OnboardingContext.Provider>
        )
    );

    return {container, unmount: () => act(() => root.unmount())};
};

describe('Onboarding as the list page banner', () => {
    it('renders no banner landmark while no dialog shows, so the page has no empty gap', async () => {
        const {container, unmount} = await renderHeader(false);

        expect(container.querySelector('section[role="banner"]')).toBeNull();

        await unmount();
    });

    it('renders the dialog in the banner landmark while it shows', async () => {
        const {container, unmount} = await renderHeader(true);

        expect(container.querySelector('section[role="banner"]')?.textContent).toBe('Feature notice');

        await unmount();
    });
});
