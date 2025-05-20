import { useEffect, useState } from 'react';
import type { PressEvent } from 'react-aria-components';
import { TabsContext, LinkContext, Tabs } from 'react-aria-components';
import donorDetailsTabs from './definitions';
import { DonorDetailsTab } from '../types';

const tabs: DonorDetailsTab[] = donorDetailsTabs;

export default function TabsRouter({ children }: { children: React.ReactNode }) {
    const [activeTab, setActiveTab] = useState<DonorDetailsTab>(tabs[0]);
    const [selectedKey, onSelectionChange] = useState(null);

    const onPress = (e: PressEvent) => {
        onSelectionChange(e.target.getAttribute('data-href'));
    };

    const getTabFromURL = () => {
        const urlParams = new URLSearchParams(window.location.search);
        const tabId = urlParams.get('tab') || activeTab.id;
        return tabs.find((tab) => tab.id === tabId);
    };

    const handleTabNavigation = (tabId: string) => {
        const newTab = tabs.find((tab) => tab.id === tabId);

        if (!newTab) {
            return;
        }

        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('tab', newTab.id);

        window.history.pushState(null, activeTab.title, `${window.location.pathname}?${urlParams.toString()}`);

        setActiveTab(newTab);
    };

    const handleUrlTabParamOnFirstLoad = () => {
        const urlParams = new URLSearchParams(window.location.search);
        // Add the 'tab' parameter only if it's not in the URL yet
        if (!urlParams.has('tab')) {
            urlParams.set('tab', activeTab.id);
            window.history.replaceState(null, activeTab.title, `${window.location.pathname}?${urlParams.toString()}`);
        } else {
            setActiveTab(getTabFromURL());
        }
    };

    useEffect(() => {
        handleUrlTabParamOnFirstLoad();

        const handlePopState = () => setActiveTab(getTabFromURL());

        // Updates state based on URL when user navigates with "Back" or "Forward" buttons
        window.addEventListener('popstate', handlePopState);

        // Cleanup listener on unmount
        return () => {
            window.removeEventListener('popstate', handlePopState);
        };
    }, []);

    return (
        <TabsContext.Provider value={{ selectedKey, onSelectionChange }}>
            <LinkContext.Provider value={{ onPress }}>
                <Tabs defaultSelectedKey={activeTab.id} selectedKey={activeTab.id} onSelectionChange={handleTabNavigation}>
                    {children}
                </Tabs>
            </LinkContext.Provider>
        </TabsContext.Provider>
    );
}
