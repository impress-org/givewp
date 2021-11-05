import {Button} from './Button';
import {Card} from './Card';
import {transformStrong} from './utils';

import styles from './PricingPlanCard.module.css'

export const PricingPlanCard = ({name, description, actionText, actionLink, icon}) => (
    <Card as="article">
        <img className={styles.icon} src={icon} alt="" />
        <h3>{name}</h3>
        <p dangerouslySetInnerHTML={{__html: transformStrong(description)}} />
        <Button as="a" href={actionLink}>{actionText}</Button>
    </Card>
);
