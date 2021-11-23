import {__} from '@wordpress/i18n';

import {AdditionalAddonCard} from './AdditionalAddonCard';
import {Button} from './Button';
import {Hero} from './Hero';
import styles from './AdditionalAddons.module.css';

const {heading, description, addons, addonButtonCaption, allAddonsUrl} = window.GiveAddons.additionalAddons;

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
        <div className={styles.viewAll}>
            <p
                className={styles.viewAllText}
                dangerouslySetInnerHTML={{
                    __html: __('Didn’t find what you were looking for?<br> View the entire catalog!', 'give'),
                }}
            />
            <Button as="a" href={allAddonsUrl} rel="noopener" target="_blank" className={styles.viewAllButton}>
                {__('View All Add-ons', 'give')}
            </Button>
        </div>
    </article>
);
