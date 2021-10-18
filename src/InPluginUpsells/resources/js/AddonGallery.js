import {Tabs, TabList, Tab, TabPanels, TabPanel} from '@reach/tabs';
import {__} from '@wordpress/i18n';

import {MustHaveAddons} from './MustHaveAddons';
import {AdditionalAddons} from './AdditionalAddons';
import {PricingPlans} from './PricingPlans';
import giveLogo from '!!raw-loader!./givewp-logo.svg';
import styles from './AddonGallery.module.css';

const GiveWPLogo = () => <div dangerouslySetInnerHTML={{__html: giveLogo}} />;

export const AddonGallery = () => (
	<Tabs as="article" className={styles.root}>
		<div className={styles.header}>
			<div className={styles.container}>
				<h1 className="screen-reader-text">
					{__('Give Add-ons Gallery', 'give')}
				</h1>
				<GiveWPLogo />
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
