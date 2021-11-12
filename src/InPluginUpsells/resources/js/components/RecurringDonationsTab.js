import {__} from '@wordpress/i18n';

import {Card} from './Card';
import styles from './RecurringDonationsTab.module.css';

const features = [
    __('Flexible recurring giving options', 'give'),
    __('Subscription management built-in', 'give'),
    __('Advanced reporting features', 'give'),
    __('Multiple gateways supported', 'give'),
    __('Customizable subscription emails', 'give'),
    __('Stripe, PayPal, Apple and G Pay', 'give'),
];

export const RecurringDonationsTab = () => (
    <Card as="article" className={styles.root}>
        <h1 className={styles.title}>{__('Increase your fundraising with Recurring Donations', 'give')}</h1>
        <p className={styles.description}>{__('The best fundraisers and organizations know that capturing recurring donors is the foundation of your organizations longevity.', 'give')}</p>
        <Button
            as="a"
            href="https://givewp.com/addons/recurring-donations/"
            rel="noopener"
            target="_blank"
            className={styles.learnMoreButton}
        >
            {__('Learn More', 'give')}
        </Button>
        <ul className={styles.features}>
            {features.map(feature => (
                <li key={feature} className={styles.feature}>
                    <svg className={styles.featureIcon}>
                        <use href="#give-in-plugin-upsells-checkmark" />
                    </svg>
                    {feature}
                </li>
            ))}
        </ul>
    </Card>
);
