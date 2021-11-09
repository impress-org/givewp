import {PricingPlanCard} from './PricingPlanCard';
import {Hero} from './Hero';
import styles from './PricingPlans.module.css'

const {heading, description, plansButtonCaption, plans} = window.GiveAddons.pricingPlans;

export const PricingPlans = () => (
	<article>
        <Hero heading={heading} description={description} />
        <ul className={styles.plans}>
            {plans.map((plan) => (
                <li name={plan.name}>
                    <PricingPlanCard
                        name={plan.name}
                        description={plan.description}
                        actionText={plansButtonCaption}
                        actionLink={plan.url}
                        icon={plan.icon}
                        savingsPercentage={plan.savingsPercentage}
                    />
                </li>
            ))}
        </ul>
	</article>
);

