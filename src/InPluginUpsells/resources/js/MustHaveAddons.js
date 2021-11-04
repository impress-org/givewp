import {__} from '@wordpress/i18n';

import {AddonCard} from './AddonCard';
import styles from './MustHaveAddons.module.css';

const {addonButtonCaption, addons, description, heading} = GiveAddons.mustHaveAddons;

export const MustHaveAddons = () => (
	<article>
		<div className={styles.hero}>
			<h2 className={styles.title}>{heading}</h2>
			<p className={styles.description}>{description}</p>
		</div>
		<ul className={styles.grid}>
			{addons.map(({name, description, url, icon, image, features}) => (
                <li key={name}>
                    <AddonCard
                        name={name}
                        description={description}
                        icon={icon}
                        image={image}
                        features={features}
                        actionLink={url}
                        actionText={addonButtonCaption}
                    />
                </li>
            ))}
		</ul>
	</article>
);

