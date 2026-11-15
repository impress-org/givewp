import {__} from '@wordpress/i18n';
import Balloon from '@givewp/components/ListTable/Balloon';

import styles from './Notices.module.scss';

/**
 * @since TBD Render through the shared Balloon in the stock WordPress look.
 */
export default ({handleClick}) => (
    <Balloon
        className={`${styles.tooltip} ${styles.defaultForm}`}
        title={__('Default campaign form', 'give')}
        onDismiss={handleClick}
    >
        {__(
            'The default form will always appear at the top of this list. Your campaign page and blocks will collect donations through this form by default. You can change it at any time.',
            'give'
        )}
    </Balloon>
);
