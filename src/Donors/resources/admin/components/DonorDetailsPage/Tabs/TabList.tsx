import { __ } from '@wordpress/i18n';
import { Tab, TabList, TabsContext } from 'react-aria-components';
import donorDetailsTabs from './definitions';
import { DonorDetailsTab } from '../types';
import styles from '../DonorDetailsPage.module.scss';
import { useContext } from 'react';

const tabs: DonorDetailsTab[] = donorDetailsTabs;

export default function DonorDetailsPageTabList() {
    // @ts-ignore
    const {selectedKey} = useContext(TabsContext);
    const activeTab = tabs.find((tab) => tab.id === selectedKey);
    const isFullWidth = activeTab?.fullwidth;

    return (
        <TabList className={`${styles.tabs} ${isFullWidth ? styles.fullWidth : ''}`}>
            {tabs.map((tab) => (
                <Tab key={tab.id} id={tab.id} data-href={`#${tab.id}`}>
                    {tab.title}
                </Tab>
            ))}
        </TabList>
    );
}
