import {detailsPageTab, GiveCampaignDetails} from './types';
import styles from './CampaignDetailsPage.module.scss';
import {__} from '@wordpress/i18n';
import {useEffect, useState} from 'react';
import cx from 'classnames';

declare const window: {
    GiveCampaignDetails: GiveCampaignDetails;
} & Window;

export function getGiveCampaignDetailsWindowData() {
    return window.GiveCampaignDetails;
}

const {adminUrl, campaign} = getGiveCampaignDetailsWindowData();

const tabs: detailsPageTab[] = [
    {
        id: 'overview',
        title: __('Overview', 'give'),
        content: () => (
            <>
                <p>Overview component goes here...</p>
                <ul>
                    {Object.entries(campaign.properties).map(([property, value], index) => (
                        <li key={index}>
                            <span>
                                <strong>{property}:</strong> {String(value)}
                            </span>
                        </li>
                    ))}
                </ul>
            </>
        ),
    },
    {
        id: 'settings',
        title: __('Settings', 'give'),
        content: () => (
            <>
                <p>Settings component goes here...</p>
                <p>
                    <a
                        style={{fontSize: '1.5rem'}}
                        href={campaign.settings.landingPageUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Edit Campaign Landing Page ⭷
                    </a>
                </p>
            </>
        ),
    },
    {
        id: 'forms',
        title: __('Forms', 'give'),
        content: () => (
            <>
                <p>Forms list table goes here...</p>
            </>
        ),
    },
];

export default function CampaignsDetailsPage() {
    const [activeTab, setActiveTab] = useState<detailsPageTab>(tabs[0]);

    const getTabFromURL = () => {
        const urlParams = new URLSearchParams(window.location.search);
        const tabId = urlParams.get('tab') || activeTab.id;
        const tab = tabs.find((tab) => tab.id === tabId);
        console.log('tab: ', tab);

        return tab;
    };

    const handleTabNavigation = (newTab: detailsPageTab) => {
        // @ts-ignore
        const url = new URL(window.location);
        const urlParams = new URLSearchParams(url.search);

        if (newTab) {
            urlParams.set('tab', newTab.id);
        } else {
            urlParams.delete('tab');
        }

        const newUrl = `${url.pathname}?${urlParams.toString()}`;
        window.history.pushState(null, activeTab.title, newUrl);

        setActiveTab(newTab);
    };

    const handleUrlTabParamOnFirstLoad = () => {
        // @ts-ignore
        const url = new URL(window.location);
        const urlParams = new URLSearchParams(url.search);

        // Add the 'tab' parameter only if it's not in the URL yet
        if (!urlParams.has('tab')) {
            urlParams.set('tab', activeTab.id);
            const newUrl = `${url.pathname}?${urlParams.toString()}`;
            window.history.replaceState(null, activeTab.title, newUrl);
        } else {
            setActiveTab(getTabFromURL());
        }
    };

    useEffect(() => {
        handleUrlTabParamOnFirstLoad();

        const handlePopState = () => {
            console.log('handlePopState');
            setActiveTab(getTabFromURL());
        };

        // Updates state based on URL when user navigates with "Back" or "Forward" buttons
        window.addEventListener('popstate', handlePopState);

        // Cleanup listener on unmount
        return () => {
            window.removeEventListener('popstate', handlePopState);
        };
    }, []);

    return (
        <>
            <article className={styles.page}>
                <header className={styles.pageHeader}>
                    <div className={styles.breadcrumb}>
                        {' '}
                        {` ${__('Campaigns', 'give')} > ${campaign.properties.title}`}
                    </div>
                    <div className={styles.flexContainer}>
                        <div className={styles.flexRow}>
                            <h1 className={styles.pageTitle}>{__('Campaign details', 'give')}</h1>
                        </div>

                        <div className={styles.flexRow}>
                            <a
                                href={`${adminUrl}edit.php?post_type=give_forms&page=give-campaigns`}
                                className={`button button-secondary ${styles.updateCampaignsButton}`}
                            >
                                {__('Save', 'give')}
                            </a>
                        </div>
                    </div>
                </header>
                <div className={cx('wp-header-end', 'hidden')} />

                <nav className={styles.tabsNav}>
                    {Object.values(tabs).map((tab) => (
                        <button
                            key={tab.id}
                            className={cx(styles.tabButton, activeTab === tab && styles.activeTab)}
                            onClick={() => handleTabNavigation(tab)}
                        >
                            {tab.title}
                        </button>
                    ))}
                </nav>

                <div className={styles.pageContent}>
                    <activeTab.content />
                </div>
            </article>
        </>
    );
}
