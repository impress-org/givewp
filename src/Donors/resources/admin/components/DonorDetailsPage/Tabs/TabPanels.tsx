import { __ } from '@wordpress/i18n';
import { TabPanel, TabsContext } from 'react-aria-components';
import donorDetailsTabs from './definitions';
import { DonorDetailsTab } from '../types';
import DonorDetailsErrorBoundary from '../Components/DonorDetailsErrorBoundary';
import styles from '../DonorDetailsPage.module.scss';
import { useContext } from 'react';

const tabs: DonorDetailsTab[] = donorDetailsTabs;

export default function DonorDetailsPageTabPanels() {
    // @ts-ignore
    const {selectedKey} = useContext(TabsContext);
    const activeTab = tabs.find((tab) => tab.id === selectedKey);
    const isFullWidth = activeTab?.fullwidth;

    return (
        <div className={`${styles.pageContent} ${isFullWidth ? styles.fullWidth : ''}`}>
            <DonorDetailsErrorBoundary>
                {tabs.map((tab) => (
                    <TabPanel key={tab.id} id={tab.id}>
                        <tab.content />
                    </TabPanel>
                ))}
            </DonorDetailsErrorBoundary>
        </div>
    );
}
