import {Button} from './Button';
import {Card} from './Card';

import styles from './AdditionalAddonCard.module.css';

export const AdditionalAddonCard = ({name, description, image, actionLink, actionText}) => (
    <Card as="article">
        <img src={image} alt="" />
        <h3 className={styles.title}>{name}</h3>
        <p className={styles.description}>{description}</p>
        <Button as="a" href={actionLink}>
            {actionText}
        </Button>
    </Card>
);
