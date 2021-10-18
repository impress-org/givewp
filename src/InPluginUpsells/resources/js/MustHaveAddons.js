import {__} from '@wordpress/i18n';

import {AddonCard} from './AddonCard';
import styles from './MustHaveAddons.module.css';
import ADDONS from './mock-addons.json';

export const MustHaveAddons = () => (
	<article>
		<div className={styles.hero}>
			<h2 className={styles.title}>
				{__('Ready to take your fundraising to the next level?', 'give')}
			</h2>
			<p className={styles.description}>
				{__('Recurring donations, fee recovery, and more powerful add-ons to power your campaigns from A to Z.', 'give')}
			</p>
		</div>
		<ul className={styles.grid}>
			{ADDONS.map(addon => <li key={addon.name}><AddonCard {...addon} /></li>)}
		</ul>
	</article>
);

