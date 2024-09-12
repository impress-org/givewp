import {GiveCampaignDetails} from './types';
import styles from './CampaignDetailsPage.module.scss';
import {__} from '@wordpress/i18n';
import {useState} from 'react';
import cx from 'classnames';

declare const window: {
    GiveCampaignDetails: GiveCampaignDetails;
} & Window;

export function getGiveCampaignDetailsWindowData() {
    return window.GiveCampaignDetails;
}

const {adminUrl, campaignDetailsPage} = getGiveCampaignDetailsWindowData();

console.log(Object.values(campaignDetailsPage.overviewTab));

const tabs = {
    overview: __('Overview', 'give'),
    settings: __('Settings', 'give'),
    forms: __('Forms', 'give'),
};

export default function CampaignsDetailsPage() {
    const [activeTab, setActiveTab] = useState<'overview' | 'settings' | 'forms'>('overview');
    const [updateErrors, setUpdateErrors] = useState<{errors: Array<number>; successes: Array<number>}>({
        errors: [],
        successes: [],
    });

    return (
        <>
            <article className={styles.page}>
                <header className={styles.pageHeader}>
                    <div className={styles.breadcrumb}>
                        {' '}
                        {` ${__('Campaigns', 'give')} > ${campaignDetailsPage.overviewTab.title}`}
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
                    {Object.keys(tabs).map((tab) => (
                        <button
                            key={tab}
                            className={cx(styles.tabButton, activeTab === tab && styles.activeTab)}
                            onClick={() => setActiveTab(tab as 'overview' | 'settings' | 'forms')}
                        >
                            {tabs[tab]}
                        </button>
                    ))}
                </nav>

                <div className={styles.pageContent}>
                    {activeTab === 'settings' ? (
                        <>
                            <p>Settings component goes here...</p>
                            <p>
                                <a
                                    style={{fontSize: '1.5rem'}}
                                    href={campaignDetailsPage.settingsTab.landingPageUrl}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    Edit Campaign Landing Page ⭷
                                </a>
                            </p>
                        </>
                    ) : (
                        <>
                            <p>Overview component goes here...</p>

                            <ul>
                                {Object.entries(campaignDetailsPage.overviewTab).map(([property, value], index) => (
                                    <li key={index}>
                                        <span>
                                            <strong>{property}:</strong> {String(value)}
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        </>
                    )}
                </div>
            </article>
        </>
    );
}
