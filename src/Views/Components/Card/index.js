// Import vendor dependencies
import PropTypes from 'prop-types';
// Import styles
import styles from './style.module.scss';

const Card = ({width = 4, title = null, children = null}) => {
    return (
        <div className={styles.card} style={{gridColumn: 'span ' + width}}>
            {title && <div className={styles.title}>{title}</div>}
            <div className={styles.content}>{children}</div>
        </div>
    );
};

Card.propTypes = {
    // Number of grid columns for Card to span, out of 12
    width: PropTypes.number,
    // Title of card
    title: PropTypes.string,
    // Elements to displayed in content area of card (eg: Chart, List)
    children: PropTypes.node.isRequired,
};

export default Card;
