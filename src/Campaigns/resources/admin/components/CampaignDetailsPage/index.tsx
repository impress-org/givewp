import {__} from '@wordpress/i18n';
import {useDispatch, useSelect} from '@wordpress/data';
import {useEffect, useState} from '@wordpress/element';
import cx from 'classnames';
import {CampaignDetailsTab, GiveCampaignDetails} from './types';

import campaignDetailsTabs from './tabs';
import CampaignsApi from '../api';
import styles from './CampaignDetailsPage.module.scss';

declare const window: {
    GiveCampaignDetails: GiveCampaignDetails;
} & Window;

export function getGiveCampaignDetailsWindowData() {
    return window.GiveCampaignDetails;
}

const {adminUrl, apiRoot, apiNonce} = getGiveCampaignDetailsWindowData();
const API = new CampaignsApi({apiNonce, apiRoot});
const tabs: CampaignDetailsTab[] = campaignDetailsTabs;

export default function CampaignsDetailsPage() {

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

    const [activeTab, setActiveTab] = useState<CampaignDetailsTab>(tabs[0]);
    const [submitting, setSubmitting] = useState(false);

    const store = useSelect(select => select('givewp/campaigns'), []);
    const actions = useDispatch('givewp/campaigns');

    const campaign = store.getCampaign();

    console.log(campaign.title)


    const getTabFromURL = () => {
        const urlParams = new URLSearchParams(window.location.search);
        const tabId = urlParams.get('tab') || activeTab.id;
        const tab = tabs.find((tab) => tab.id === tabId);
        console.log('tab: ', tab);

        return tab;
    };

    const handleTabNavigation = (newTab: CampaignDetailsTab) => {
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

    const publishCampaign: any = async () => {
        try {
            setSubmitting(true);
            const endpoint = `/publish/${campaign.properties.id}`;
            const response = await API.fetchWithArgs(endpoint, {}, 'PUT');
            console.log('Campaign published', response);
            location.reload();
        } catch (error) {
            setSubmitting(false);
            console.error('Error publishing campaign campaign', error);
        }
    };

    const updateCampaign: any = async () => {
        try {
            setSubmitting(true);
            const endpoint = `/${campaign.properties.id}`;
            const response = await API.fetchWithArgs(
                endpoint,
                {
                    title: 'Random ' + Math.random(),
                },
                'PUT',
            );
            console.log('Campaign updated', response);
            location.reload();
        } catch (error) {
            setSubmitting(false);
            console.error('Error updating campaign campaign', error);
        }
    };

    return (
        <>
            <article className={styles.page}>
                <header className={styles.pageHeader} onClick={() => actions.updateCampaign({title: 'Title ' + Math.random()})}>
                    <div className={styles.breadcrumb}>
                        <a href={`${adminUrl}edit.php?post_type=give_forms&page=give-campaigns`}>
                            {__('Campaigns', 'give')}
                        </a>
                        {' > '}
                        <span>{campaign.title}</span>
                    </div>
                    <div className={styles.flexContainer}>
                        <div className={styles.flexRow}>
                            <h1 className={styles.pageTitle}>{campaign.title}</h1>
                        </div>

                        <div className={styles.flexRow}>
                            {campaign.status === 'draft' && (
                                <button
                                    disabled={submitting}
                                    className={`button button-secondary ${styles.button} ${styles.updateCampaignButton}`}
                                    onClick={updateCampaign}
                                >
                                    {__('Save as draft', 'give')}
                                </button>
                            )}
                            <button
                                disabled={submitting}
                                className={`button button-primary ${styles.button} ${styles.updateCampaignButton}`}
                                onClick={campaign.status === 'draft' ? publishCampaign : updateCampaign}
                            >
                                {campaign.status === 'draft'
                                    ? __('Publish campaign', 'give')
                                    : __('Update campaign', 'give')}
                            </button>
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
