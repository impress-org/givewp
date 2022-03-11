import {__} from '@wordpress/i18n';

import {Button} from './Button';
import {Card} from './Card';
import styles from './RecurringDonationsTab.module.scss';

const features = [
    __('Flexible recurring giving options', 'give'),
    __('Subscription management built-in', 'give'),
    __('Advanced reporting features', 'give'),
    __('Multiple gateways supported', 'give'),
    __('Customizable subscription emails', 'give'),
    __('Stripe, PayPal, Apple and G Pay', 'give'),
];

export const RecurringDonationsTab = () => (
    <Card as="article" className={styles.card}>
        <h2 className={styles.title}>{__('Increase your fundraising with Recurring Donations', 'give')}</h2>
        <p className={styles.description}>
            {__(
                'The best fundraisers and organizations know that capturing recurring donors is the foundation of your organizations longevity.',
                'give'
            )}
        </p>
        <Button
            as="a"
            href="https://docs.givewp.com/acrecurring"
            rel="noopener"
            target="_blank"
            className={styles.learnMoreButton}
        >
            {__('Learn More', 'give')}
        </Button>
        <img
            className={styles.image}
            src={`${window.GiveRecurringDonations.assetsUrl}images/admin/recurring-upsell-graphic.png`}
            alt=""
        />
        <ul className={styles.features}>
            {features.map((feature) => (
                <li key={feature} className={styles.feature}>
                    <svg className={styles.featureIcon} viewBox="0 0 16 12" preserveAspectRatio="xMinYMax meet">
                        <use href="#give-in-plugin-upsells-checkmark" />
                    </svg>
                    {feature}
                </li>
            ))}
        </ul>
    </Card>
);
