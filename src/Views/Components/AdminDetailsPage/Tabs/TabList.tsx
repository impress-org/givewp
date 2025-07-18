import { Tab, TabList as ReactAriaTabList, TabsContext } from 'react-aria-components';
import { Tab as TabType } from '../types';
import styles from '../AdminDetailsPage.module.scss';
import { useContext } from 'react';

/**
 * @since 4.4.0
 */
export default function TabList({ tabDefinitions }: { tabDefinitions: TabType[] }) {
    // @ts-ignore
    const {selectedKey} = useContext(TabsContext);
    const activeTab = tabDefinitions.find((tab) => tab.id === selectedKey);
    const isFullWidth = activeTab?.fullwidth;

    return (
        <ReactAriaTabList className={`${styles.tabs} ${isFullWidth ? styles.fullWidth : ''}`}>
            {tabDefinitions.map((tab) => (
                <Tab key={tab.id} id={tab.id} data-href={`#${tab.id}`} data-text={tab.title}>
                    {tab.title}
                </Tab>
            ))}
        </ReactAriaTabList>
    );
}
