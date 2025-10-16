import { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { Tab, TabList, TabPanel, Tabs } from 'react-aria-components';
import GeneralTab from './General';
import AdditionalInfoTab from './AdditionalInfo';
import cx from 'classnames';
import { useTriggerResize } from '@givewp/components/hooks';

import styles from './styles.module.scss';

/**
 * @since 4.6.0
 */
export default function DonationDetailsPageRecordsTab() {
    const [selectedKey, setSelectedKey] = useState('general');

    useTriggerResize(selectedKey);

    const subtabs = [
        {
            id: 'general',
            title: __('General', 'give'),
            content: GeneralTab,
        },
        {
            id: 'additional-info',
            title: __('Additional Info', 'give'),
            content: AdditionalInfoTab,
        },
    ];

    return (
        <Tabs selectedKey={selectedKey} onSelectionChange={(key) => setSelectedKey(String(key))}>
            <TabList className={styles.tabList}>
                {subtabs.map((subtab) => (
                    <Tab
                        key={subtab.id}
                        id={subtab.id}
                        className={cx(styles.tabItem, selectedKey === subtab.id && styles.activeTabItem)}
                        data-text={subtab.title}
                    >
                        {subtab.title}
                    </Tab>
                ))}
            </TabList>

            <div>
                {subtabs.map((subtab) => (
                    <TabPanel key={subtab.id} id={subtab.id}>
                        <subtab.content />
                    </TabPanel>
                ))}
            </div>
        </Tabs>
    );
}
