import { __ } from '@wordpress/i18n';
import { Tab, TabList, TabsContext } from 'react-aria-components';
import { Tab as TabType } from '../types';
import styles from '../AdminDetailsPage.module.scss';
import { useContext } from 'react';

export default function DonorDetailsPageTabList({ tabDefinitions }: { tabDefinitions: TabType[] }) {
    // @ts-ignore
    const {selectedKey} = useContext(TabsContext);
    const activeTab = tabDefinitions.find((tab) => tab.id === selectedKey);
    const isFullWidth = activeTab?.fullwidth;

    return (
        <TabList className={`${styles.tabs} ${isFullWidth ? styles.fullWidth : ''}`}>
            {tabDefinitions.map((tab) => (
                <Tab key={tab.id} id={tab.id} data-href={`#${tab.id}`}>
                    {tab.title}
                </Tab>
            ))}
        </TabList>
    );
}
