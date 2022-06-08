import {__} from '@wordpress/i18n';
import {StellarDiscountsCard} from './StellarDiscountsCard';
import styles from './StellarDiscounts.module.css';

const {stellarLogo, stellarLink, discounts, description, heading} = window.GiveAddons.stellarDiscounts;

export const StellarDiscounts = () => (
    <article>
        <div className={styles.stellarHero}>
            <div className={styles.stellarLogo}>
                <a href={stellarLink} target="_blank" rel="noopener">
                    <img src={stellarLogo} alt={__('Stellar', 'give')} className={styles.logo} />
                </a>
            </div>
            <h2 className={styles.stellarIntro}>{heading}</h2>
            <p className={styles.description}>{description}</p>
        </div>
        <ul className={styles.grid}>
            {discounts.map(({description, image, link, linkText, logo, title}) => (
                <li key={title}>
                    <StellarDiscountsCard
                        description={description}
                        logo={logo}
                        image={image}
                        actionLink={link}
                        actionText={linkText}
                        title={title}
                    />
                </li>
            ))}
        </ul>
    </article>
);
