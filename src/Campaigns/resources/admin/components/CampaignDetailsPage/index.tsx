import {CampaignDetailsInputs, CampaignDetailsTab, GiveCampaignDetails} from './types';
import styles from './CampaignDetailsPage.module.scss';
import {__} from '@wordpress/i18n';
import {useEffect, useState} from 'react';
import cx from 'classnames';
import campaignDetailsTabs from './tabs';
import CampaignsApi from '../api';
import {FormProvider, SubmitHandler, useForm} from 'react-hook-form';

declare const window: {
    GiveCampaignDetails: GiveCampaignDetails;
} & Window;

export function getGiveCampaignDetailsWindowData() {
    return window.GiveCampaignDetails;
}

const {adminUrl, campaign, apiRoot, apiNonce} = getGiveCampaignDetailsWindowData();
const API = new CampaignsApi({apiNonce, apiRoot});
const tabs: CampaignDetailsTab[] = campaignDetailsTabs;

export default function CampaignsDetailsPage() {
    /**
     * TABS LOGIC
     */
    const [activeTab, setActiveTab] = useState<CampaignDetailsTab>(tabs[0]);

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

    /**
     * FORM LOGIC
     */
    const [isPublishMode, setIsPublishMode] = useState(false);

    const methods = useForm<CampaignDetailsInputs>({
        defaultValues: {
            title: campaign.properties.title ?? '',
        },
    });
    const {formState, handleSubmit, watch} = methods;
    const formWatch = watch();

    useEffect(() => {
        console.log('formWatch: ', formWatch);
        console.log('formState.dirtyFields: ', formState.dirtyFields);
        console.log('formState.isDirty: ', formState.isDirty);
    }, [formWatch]);

    const onSubmit: SubmitHandler<CampaignDetailsInputs> = async (campaignDetailsInputs, event) => {
        event.preventDefault();

        try {
            if (isPublishMode) {
                console.log('publishing...');
                const endpoint = `/publish/${campaign.properties.id}`;
                const response = await API.fetchWithArgs(endpoint, {}, 'PUT');
                console.log('Campaign published.', response);
                location.reload();
            } else if (formState.isDirty) {
                console.log('updating...');
                const endpoint = `/${campaign.properties.id}`;
                const response = await API.fetchWithArgs(endpoint, campaignDetailsInputs, 'PUT');
                console.log('Campaign updated.', response);
                location.reload();
            }
        } catch (error) {
            console.error('Error updating campaign.', error);
        }
    };

    return (
        <FormProvider {...methods}>
            <form onSubmit={handleSubmit(onSubmit)}>
                <article className={styles.page}>
                    <header className={styles.pageHeader}>
                        <div className={styles.breadcrumb}>
                            <a href={`${adminUrl}edit.php?post_type=give_forms&page=give-campaigns`}>
                                {__('Campaigns', 'give')}
                            </a>
                            {' > '}
                            <span>{campaign.properties.title}</span>
                        </div>
                        <div className={styles.flexContainer}>
                            <div className={styles.flexRow}>
                                <h1 className={styles.pageTitle}>{campaign.properties.title}</h1>
                                <span
                                    className={cx(
                                        styles.status,
                                        campaign.properties.status === 'draft'
                                            ? styles.draftStatus
                                            : styles.activeStatus
                                    )}
                                >
                                    {campaign.properties.status}
                                </span>
                            </div>

                            <div className={styles.flexRow}>
                                {campaign.properties.status === 'draft' && (
                                    <button
                                        disabled={formState.isSubmitting || !formState.isDirty}
                                        className={`button button-secondary ${styles.button} ${styles.updateCampaignButton}`}
                                    >
                                        {__('Save as draft', 'give')}
                                    </button>
                                )}
                                <button
                                    onClick={() => {
                                        campaign.properties.status === 'draft'
                                            ? setIsPublishMode(true)
                                            : setIsPublishMode(false);
                                    }}
                                    disabled={
                                        campaign.properties.status !== 'draft' &&
                                        (formState.isSubmitting || !formState.isDirty)
                                    }
                                    className={`button button-primary ${styles.button} ${styles.updateCampaignButton}`}
                                >
                                    {campaign.properties.status === 'draft'
                                        ? __('Publish campaign', 'give')
                                        : __('Update campaign', 'give')}
                                </button>
                            </div>
                        </div>
                    </header>
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
                    <div className={cx('wp-header-end', 'hidden')} />

                    <div className={styles.pageContent}>
                        <activeTab.content />
                    </div>
                </article>
            </form>
        </FormProvider>
    );
}
