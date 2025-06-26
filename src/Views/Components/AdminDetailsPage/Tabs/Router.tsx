import { useEffect, useState } from 'react';
import type { PressEvent } from 'react-aria-components';
import { TabsContext, LinkContext, Tabs } from 'react-aria-components';
import { Tab } from '../types';

/**
 * @since 4.4.0
 */
export default function TabsRouter({ children, tabDefinitions }: { children: React.ReactNode, tabDefinitions: Tab[] }) {
    const [selectedKey, onSelectionChange] = useState(tabDefinitions[0].id);

    const onPress = (e: PressEvent) => {
        onSelectionChange(e.target.getAttribute('data-href'));
    };

    const getTabFromURL = () => {
        const urlParams = new URLSearchParams(window.location.search);
        const tabId = urlParams.get('tab') || selectedKey;
        return tabDefinitions.find((tab) => tab.id === tabId);
    };

    const handleTabNavigation = (tabId: string) => {
        const newTab = tabDefinitions.find((tab) => tab.id === tabId);

        if (!newTab) {
            return;
        }

        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('tab', newTab.id);

        window.history.pushState(null, newTab.title, `${window.location.pathname}?${urlParams.toString()}`);

        onSelectionChange(newTab.id);
    };

    const handleUrlTabParamOnFirstLoad = () => {
        const activeTab = tabDefinitions.find((tab) => tab.id === selectedKey);
        const urlParams = new URLSearchParams(window.location.search);
        // Add the 'tab' parameter only if it's not in the URL yet
        if (!urlParams.has('tab')) {
            urlParams.set('tab', selectedKey);
            window.history.replaceState(null, activeTab?.title, `${window.location.pathname}?${urlParams.toString()}`);
        } else {
            onSelectionChange(getTabFromURL()?.id);
        }
    };

    useEffect(() => {
        handleUrlTabParamOnFirstLoad();

        const handlePopState = () => onSelectionChange(getTabFromURL()?.id);

        // Updates state based on URL when user navigates with "Back" or "Forward" buttons
        window.addEventListener('popstate', handlePopState);

        // Cleanup listener on unmount
        return () => {
            window.removeEventListener('popstate', handlePopState);
        };
    }, []);

    return (
        <TabsContext.Provider value={{ selectedKey, onSelectionChange: handleTabNavigation }}>
            <LinkContext.Provider value={{ onPress }}>
                <Tabs>
                    {children}
                </Tabs>
            </LinkContext.Provider>
        </TabsContext.Provider>
    );
}
