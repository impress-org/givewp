import {useMemo, useState} from 'react';
import {Tab, TabList, TabPanel, Tabs} from 'react-aria-components';
import {__, sprintf} from '@wordpress/i18n';

import {MustHaveAddons} from './MustHaveAddons';
import {AdditionalAddons} from './AdditionalAddons';
import {PricingPlans} from './PricingPlans';
import {FreeAddOnTab} from './FreeAddOnTab';
import {assetUrl} from '../utils';
import styles from './AddonsAdminPage.module.css';

export function AddonsAdminPage({startingTab = 0}) {
    // We control the tabs only so we can use `tabIndex` to change the decor.
    const [tabIndex, setTabIndex] = useState(startingTab);
    const addonDecorAssets = useMemo(
        () =>
            [
                'images/addons-admin-page-decor-1.png',
                'images/addons-admin-page-decor-2.png',
                'images/addons-admin-page-decor-3.png',
                'images/addons-admin-page-decor-4.png',
            ].map(assetUrl),
        []
    );

    return (
        <Tabs
            as="article"
            style={{'--decor': `url("${addonDecorAssets[tabIndex]}")`}}
            className={styles.root}
            index={tabIndex}
            onChange={setTabIndex}
        >
            <div className={styles.header}>
                <div className={styles.container}>
                    <h1>
                        <a href="https://givewp.com" target="_blank" rel="noopener">
                            <img
                                className={styles.logo}
                                src={assetUrl('images/givewp-logo.png')}
                                alt={__('GiveWP', 'give')}
                            />
                        </a>
                    </h1>
                    <aside
                        className={styles.sourceNotice}
                        dangerouslySetInnerHTML={{
                            __html: sprintf(
                                /* translators: 1: Text before anchor html tag 2: Open anchor html tag 3: Website name 4: Close anchor tag*/
                                '%1$s %2$s%3$s%4$s',
                                __('This page is loaded from', 'give'),
                                '<a href="https://givewp.com" rel="noopener" target="_blank">',
                                __('GiveWP.com', 'give'),
                                '</a>'
                            ),
                        }}
                    />
                    <TabList className={styles.tabs}>
                        <Tab id="tab-1">{__('View Pricing Plans', 'give')}</Tab>
                        <Tab id="tab-2">{__('Must Have Add-ons', 'give')}</Tab>
                        <Tab id="tab-3">{__('Additional Add-ons', 'give')}</Tab>
                        <Tab id="tab-4">{__('Get a Free Add-on!', 'give')}</Tab>
                    </TabList>
                </div>
            </div>
            <div className={styles.container}>
                <TabPanel id="tab-1">
                    <PricingPlans />
                </TabPanel>
                <TabPanel id="tab-2">
                    <MustHaveAddons />
                </TabPanel>
                <TabPanel id="tab-3">
                    <AdditionalAddons />
                </TabPanel>
                <TabPanel id="tab-4">
                    <FreeAddOnTab />
                </TabPanel>
            </div>
        </Tabs>
    );
}
