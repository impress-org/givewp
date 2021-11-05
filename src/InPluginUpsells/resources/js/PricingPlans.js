import {PricingPlanCard} from './PricingPlanCard';
import {Hero} from './Hero';
import styles from './PricingPlans.module.css'

const {heading, description, plansButtonCaption, plans} = window.GiveAddons.pricingPlans;

export const PricingPlans = () => (
	<article>
        <Hero heading={heading} description={description} />
        <ul className={styles.plans}>
            {plans.map(({name, description, url, icon}) => (
                <li name={name}>
                    <PricingPlanCard
                        name={name}
                        description={description}
                        actionText={plansButtonCaption}
                        actionLink={url}
                        icon={icon}
                    />
                </li>
            ))}
        </ul>
	</article>
);

