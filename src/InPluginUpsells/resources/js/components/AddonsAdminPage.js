import {useMemo, useState} from 'react';
import {Tabs, TabList, Tab, TabPanels, TabPanel} from '@reach/tabs';
import {__} from '@wordpress/i18n';

import {MustHaveAddons} from './MustHaveAddons';
import {AdditionalAddons} from './AdditionalAddons';
import {PricingPlans} from './PricingPlans';
import {assetUrl} from '../utils';
import styles from './AddonsAdminPage.module.css';

export function AddonsAdminPage() {
    // We control the tabs only so we can use `tabIndex` to change the decor.
    const [tabIndex, setTabIndex] = useState(0);
    const addonDecorAssets = useMemo(
        () =>
            [
                'images/addons-admin-page-decor-1.png',
                'images/addons-admin-page-decor-2.png',
                'images/addons-admin-page-decor-3.png',
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
                            __html: __(
                                'This page is loaded from <a href="https://givewp.com" rel="noopener" target="_blank">GiveWP.com</a>'
                            ),
                        }}
                    />
                    <TabList className={styles.tabs}>
                        <Tab>{__('Must Have Add-ons', 'give')}</Tab>
                        <Tab>{__('View Pricing Plans', 'give')}</Tab>
                        <Tab>{__('Additional Add-ons', 'give')}</Tab>
                    </TabList>
                </div>
            </div>
            <TabPanels className={styles.container}>
                <TabPanel>
                    <MustHaveAddons />
                </TabPanel>
                <TabPanel>
                    <PricingPlans />
                </TabPanel>
                <TabPanel>
                    <AdditionalAddons />
                </TabPanel>
            </TabPanels>
        </Tabs>
    );
}
