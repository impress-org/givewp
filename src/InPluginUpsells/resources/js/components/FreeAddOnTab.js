import {useRef, useState} from 'react';
import axios from 'axios';
import cx from 'classnames';

import styles from './FreeAddOnTab.module.css';
import {Hero} from './Hero';
import {Card} from './Card';
import {transformStrong} from '../utils';

const highlights = [
    {
        icon: 'https://givewp.com/downloads/upsells/images/time-icon.svg',
        text: 'Flexible Delivery Times',
    },
    {
        icon: 'https://givewp.com/downloads/upsells/images/trend-icon.svg',
        text: 'Pack your Reports with Stats',
    },
    {
        icon: 'https://givewp.com/downloads/upsells/images/people-icon.svg',
        text: 'Customize the Content & Recipients',
    },
];

const description = [
    'Send informative email reports to your team, how you like, when you like.',
    '**Normally $79**',
    'You get it today for free!',
];

const {siteUrl, siteName} = window.GiveAddons;

export const FreeAddOnTab = () => {
    const [userSubscribed, setUserSubscribed] = useState(false);
    const [hasSubmissionError, setHasSubmissionError] = useState(false);
    const firstNameInput = useRef();
    const emailInput = useRef();

    const handleSubscribe = async (event) => {
        event.preventDefault();

        try {
            const response = await axios.post('https://givewp-gateway.local/activecampaign/subscribe/free-add-on', {
                first_name: firstNameInput.current.value,
                email: emailInput.current.value,
                website_url: siteUrl,
                website_name: siteName,
            });

            setUserSubscribed(true);
            console.log(response);
        } catch (error) {
            setHasSubmissionError(true);
            console.error(error);
        }
    };

    return (
        <article className={styles.freeAddonSection}>
            <Hero
                heading="Join our GiveWP Fundraising Newsletter and get the Email Reports Add-on for FREE"
                description="Our newsletter is full of resources and insights to help you do better online fundraising with GiveWP."
            />
            <Card as="article" className={cx(styles.card, styles.emailReports)}>
                <div>
                    <div className={styles.nameAndFlag}>
                        <h3 className={styles.title}>
                            <img
                                className={styles.icon}
                                src="https://givewp.com/downloads/upsells/images/email-icon.svg"
                                alt="Email Icon"
                            />
                            Email Reports
                        </h3>
                    </div>
                    <div className={styles.description}>
                        {description.map((text) => (
                            <p dangerouslySetInnerHTML={{__html: transformStrong(text)}}></p>
                        ))}
                    </div>
                </div>
                <aside className={styles.includes}>
                    <h4>Add-On Highlights</h4>
                    {highlights.map(({icon, text}) => (
                        <div className={styles.nameAndFlag}>
                            <img className={styles.icon} src={icon} alt="Clock Icon" />
                            <strong>{text}</strong>
                        </div>
                    ))}
                </aside>
            </Card>
            <Hero
                heading="Enter your information below to receive a link in your inbox"
                className={styles.informationHero}
            />
            <Card className={cx(styles.card, styles.formCard)}>
                {userSubscribed ? (
                    <span className={styles.formCard__thankYou}>
                        Excellent! We have sent you an email to confirm your submission. Please confirm the email and
                        we'll send you your free add-on!
                    </span>
                ) : (
                    <>
                        {hasSubmissionError && (
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
                            <input type="submit" value="👉 Get my Free Add-on" />
                        </form>
                        <em className={styles.disclosure}>
                            * By submitting this form, you agree to be subscribed to our GiveWP Newsletter (you can
                            unsubscribe at any time. The free Email Reports add-on is only the open source installable
                            zip file. It does not include a license or access to priority support.
                        </em>
                    </>
                )}
            </Card>
        </article>
    );
};
