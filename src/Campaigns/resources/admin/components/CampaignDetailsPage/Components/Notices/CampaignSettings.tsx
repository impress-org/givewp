import {__} from '@wordpress/i18n';
import cx from 'classnames';
import {ExternalLink} from '@wordpress/components';
import {CloseIcon} from '@givewp/campaigns/admin/components/Icons';

import styles from './Notices.module.scss';

export default ({handleClick}) => (
    <div className={cx(styles['tooltip'], styles['campaignSettings'])}>
        <div className={styles['close']} onClick={handleClick}>
            <CloseIcon />
        </div>
        <h3>
            {__('Campaign Settings', 'give')}
        </h3>
        <div className={styles['content']}>
            {__('You can make changes to your campaign page, campaign details, campaign goal, and campaign theme. Publish your campaign when you’re done with your changes.', 'give')}
        </div>
        <div className={styles['content']}>
            <ExternalLink
                href="#" //todo: add link
                onClick={handleClick}
            >
                {__('Learn more about campaign and form settings', 'give')}
            </ExternalLink>
        </div>
    </div>
)

