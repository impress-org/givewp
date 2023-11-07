import {PricingPlanCard} from './PricingPlanCard';
import {Hero} from './Hero';
import styles from './PricingPlans.module.css';

const {heading, description, plansButtonCaption, plans} = window.GiveAddons.pricingPlans;

const customPlanOrder = ['Plus Plan', 'Pro Plan', 'Basic Plan', 'Agency Plan'];

const sortedPlans = plans.slice().sort((a, b) => {
    const aIndex = customPlanOrder.indexOf(a.name);
    const bIndex = customPlanOrder.indexOf(b.name);
    return aIndex - bIndex;
});

export const PricingPlans = () => (
    <article>
        <Hero heading={heading} description={description} />
        <ul className={styles.plans}>
            {sortedPlans.map((plan) => (
                <li key={plan.name}>
                    <PricingPlanCard
                        name={plan.name}
                        description={plan.description}
                        actionText={plansButtonCaption}
                        actionLink={plan.url}
                        icon={plan.icon}
                        savingsPercentage={plan.savingsPercentage}
                        includes={plan.includes}
                        isMostPopular={plan.mostPopular}
                    />
                </li>
            ))}
        </ul>
    </article>
);
