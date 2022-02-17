import {__} from '@wordpress/i18n';

import {MustHaveAddonCard} from './MustHaveAddonCard';
import {Hero} from './Hero';
import styles from './MustHaveAddons.module.css';

const {addonButtonCaption, addons, description, heading} = window.GiveAddons.mustHaveAddons;

export const MustHaveAddons = () => (
    <article>
        <Hero heading={heading} description={description} />
        <ul className={styles.grid}>
            {addons.map(({name, description, url, icon, image, features}) => (
                <li key={name}>
                    <MustHaveAddonCard
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
