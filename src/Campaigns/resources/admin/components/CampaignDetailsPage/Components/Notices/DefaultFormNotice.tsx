import {__} from '@wordpress/i18n';
import cx from 'classnames';
import {CloseIcon} from "@givewp/campaigns/admin/components/Icons";

import styles from './Notices.module.scss'

export default ({handleClick}) => (
    <div className={cx(styles['tooltip'], styles['defaultForm'])}>
        <div className={styles['close']} onClick={handleClick}>
            <CloseIcon />
        </div>
        <h3>
            {__('Default campaign form', 'give')}
        </h3>
        <div className={styles['content']}>
            {__('The default form will always appear at the top of this list. Your campaign page and blocks will collect donations through this form by default. You can change it at any time.', 'give')}
        </div>
    </div>
)

