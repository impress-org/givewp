import {Tabs, TabList, Tab, TabPanels, TabPanel} from '@reach/tabs';
import {__} from '@wordpress/i18n';

import {MustHaveAddons} from './MustHaveAddons';
import {AdditionalAddons} from './AdditionalAddons';
import {PricingPlans} from './PricingPlans';
import styles from './InPluginUpsells.module.css';

export const InPluginUpsells = () => (
	<Tabs as="article" className={styles.root}>
		<div className={styles.header}>
			<div className={styles.container}>
				<h1 className="screen-reader-text">
					{__('Give Add-ons Gallery', 'give')}
				</h1>
                <img src="" alt={__('GiveWP', 'give')} />
                <aside
                    className={styles.sourceNotice}
                    dangerouslySetInnerHTML={{
                        __html: __('This page is loaded from <a href="https://givewp.com">GiveWP.com</a>')
                    }}
                />
				<TabList className={styles.tabs}>
					<Tab>
						{__('Must Have Add-ons', 'give')}
					</Tab>
					<Tab>
						{__('View Pricing Plans', 'give')}
					</Tab>
					<Tab>
						{__('Additional Add-ons', 'give')}
					</Tab>
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
