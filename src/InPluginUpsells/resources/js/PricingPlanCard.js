import {Button} from './Button';
import {Card} from './Card';
import {transformStrong} from './utils';

import styles from './PricingPlanCard.module.css'

export const PricingPlanCard = ({name, description, actionText, actionLink, icon, savingsPercentage}) => (
    <Card as="article">
        <img className={styles.icon} src={icon} alt="" />
        <h3 className={styles.title}>{name}</h3>
        <p className={styles.description} dangerouslySetInnerHTML={{__html: transformStrong(description)}} />
        <Button as="a" href={actionLink}>{actionText}</Button>
        <p className={styles.savings}>Save over {savingsPercentage}%</p>
    </Card>
);
