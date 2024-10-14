import {useEffect, useState} from '@wordpress/element';
import {Tab, TabList, TabPanel, Tabs} from 'react-aria-components';
import cx from 'classnames';
import {CampaignDetailsTab} from '../types';

import styles from '../CampaignDetailsPage.module.scss';
import tabsDefinitions from './definitions';
import NotificationsPlaceholder from '../../Notifications';

const tabs: CampaignDetailsTab[] = tabsDefinitions;

/**
 * @unreleased
 */
export default () => {
    const [activeTab, setActiveTab] = useState<CampaignDetailsTab>(tabs[0]);

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
        <Tabs defaultSelectedKey={activeTab.id} selectedKey={activeTab.id} onSelectionChange={handleTabNavigation}>
            <div>
                <TabList className={`${styles.tabs} ${activeTab.fullwidth ? styles.fullWidth : ''}`}>
                    {Object.values(tabs).map((tab) => (
                        <Tab key={tab.id} id={tab.id}>
                            {tab.title}{' '}
                        </Tab>
                    ))}
                </TabList>
            </div>

            <div className={cx('wp-header-end', 'hidden')} />

            <NotificationsPlaceholder type="notice" />

            <div className={`${styles.pageContent} ${activeTab.fullwidth ? styles.fullWidth : ''}`}>
                {Object.values(tabs).map((tab) => (
                    <TabPanel key={tab.id} id={tab.id}>
                        <tab.content />
                    </TabPanel>
                ))}
            </div>
        </Tabs>
    );
};
