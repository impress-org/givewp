import {Button} from './Button';
import {Card} from './Card';
import styles from './StellarDiscountsCard.module.css';

export const StellarDiscountsCard = ({description, image, actionLink, actionText, logo, title}) => (
    <Card as="article" className={styles.card}>
        <img className={styles.image} src={image} alt={actionText} />
        <img className={styles.logo} src={logo} alt={title} />
        <div className={styles.descriptionWrap}>
            <p className={styles.title}>{title}</p>
            <p className={styles.description}>{description}</p>
        </div>
        <Button as="a" href={actionLink} rel="noopener" target="_blank" className={styles.button}>
            {actionText}
        </Button>
    </Card>
);
