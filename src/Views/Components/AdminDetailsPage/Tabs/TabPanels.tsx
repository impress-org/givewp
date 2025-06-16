import { __ } from '@wordpress/i18n';
import { TabPanel, TabsContext } from 'react-aria-components';
import { Tab as TabType } from '../types';
import ErrorBoundary from '../ErrorBoundary';
import styles from '../AdminDetailsPage.module.scss';
import { useContext } from 'react';

/**
 * @since 4.4.0
 */
export default function TabPanels({ tabDefinitions }: { tabDefinitions: TabType[] }) {
    // @ts-ignore
    const {selectedKey} = useContext(TabsContext);
    const activeTab = tabDefinitions.find((tab) => tab.id === selectedKey);
    const isFullWidth = activeTab?.fullwidth;

    return (
        <div className={`${styles.pageContent} ${isFullWidth ? styles.fullWidth : ''}`}>
            <ErrorBoundary>
                {tabDefinitions.map((tab) => (
                    <TabPanel key={tab.id} id={tab.id}>
                        <tab.content />
                    </TabPanel>
                ))}
            </ErrorBoundary>
        </div>
    );
}
