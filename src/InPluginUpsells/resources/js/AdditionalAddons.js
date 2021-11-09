import {AdditionalAddonCard} from './AdditionalAddonCard';
import {Hero} from './Hero';

import styles from './AdditionalAddons.module.css';

const {heading, description, addons, addonButtonCaption} = window.GiveAddons.additionalAddons;

export const AdditionalAddons = () => (
	<article>
        <Hero heading={heading} description={description} />
        <ul className={styles.addons}>
            {addons.map(({name, image, description, url}) => (
                <li key={name}>
                    <AdditionalAddonCard
                        name={name}
                        description={description}
                        image={image}
                        actionText={addonButtonCaption}
                        actionLink={url}
                    />
                </li>
            ))}
        </ul>
	</article>
);

