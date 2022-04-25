import {useRef} from 'react';
import cx from 'classnames';

import GreenButton from '@givewp/promotions/components/GreenButton';
import useFreeAddonSubscription from '@givewp/promotions/hooks/useFreeAddonSubscription';

import styles from './FreeAddOnTab.module.css';
import {Hero} from './Hero';
import {Card} from './Card';
import {transformStrong} from '../utils';

const {heading, description, reports, form} = window.GiveAddons.freeAddon;
const {siteUrl, siteName} = window.GiveAddons;

export const FreeAddOnTab = () => {
    const {userSubscribed, hasSubscriptionError, subscribeUser} = useFreeAddonSubscription();
    const firstNameInput = useRef();
    const emailInput = useRef();

    const handleSubscribe = async (event) => {
        event.preventDefault();

        await subscribeUser(firstNameInput.current.value, emailInput.current.value, siteUrl, siteName);
    };

    return (
        <article className={styles.freeAddonSection}>
            <Hero heading={heading} description={description} />
            <Card as="article" className={cx(styles.card, styles.emailReports)}>
                <div>
                    <div className={styles.nameAndFlag}>
                        <h3 className={styles.title}>
                            <img className={styles.icon} src={reports.icon} alt={reports.heading} />
                            {reports.heading}
                        </h3>
                    </div>
                    <div className={styles.description}>
                        {reports.description.map((text, index) => (
                            <p dangerouslySetInnerHTML={{__html: transformStrong(text)}} key={index}></p>
                        ))}
                    </div>
                </div>
                <aside className={styles.includes}>
                    <h4>{reports.highlights.heading}</h4>
                    {reports.highlights.items.map(({icon, text}, index) => (
                        <div className={styles.nameAndFlag} key={index}>
                            <img className={styles.icon} src={icon} alt="Icon" />
                            <strong>{text}</strong>
                        </div>
                    ))}
                </aside>
            </Card>
            <Hero heading={form.heading} className={styles.informationHero} />
            <Card className={cx(styles.card, styles.formCard)}>
                {userSubscribed ? (
                    <span className={styles.formCard__thankYou}>{form.submissionConfirmation}</span>
                ) : (
                    <>
                        {hasSubscriptionError && (
                            <div className={styles.submissionError}>
                                There was an issue submitting your information. Please try again. If the problem
                                persists, try refreshing the page. If it still doesn't work, please{' '}
                                <a href="https://givewp.com/support" target="_blank">
                                    contact support.
                                </a>
                            </div>
                        )}
                        <form onSubmit={handleSubscribe}>
                            <div className={styles.formCard__inputs}>
                                <label>
                                    <span>First Name</span>
                                    <input type="text" name="firstName" required ref={firstNameInput} />
                                </label>
                                <label>
                                    <span>Email Address</span>
                                    <input type="email" name="email" required ref={emailInput} />
                                </label>
                            </div>
                            <GreenButton as="input" text={form.submitButton} shadow />
                        </form>
                        <em className={styles.disclosure}>{form.disclaimer}</em>
                    </>
                )}
            </Card>
        </article>
    );
};
