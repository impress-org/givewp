import {__} from '@wordpress/i18n';
import cx from 'classnames';
import {ExternalLink} from '@wordpress/components';
import {CloseIcon} from '@givewp/campaigns/admin/components/Icons';

import styles from './Notices.module.scss';

export default ({handleClick}) => (
    <div className={cx(styles['tooltip'], styles['campaignList'])}>
        <div className={styles['close']} onClick={handleClick}>
            <CloseIcon />
        </div>
        <h3>
            {__('Campaign List', 'give')}
        </h3>
        <div className={styles['content']}>
            {__('We\'ve created a campaign from each of your donation forms. Your forms still work as before, but now with the added power of campaign management! Select a campaign to see how you can seamlessly manage your online fundraising.', 'give')}
        </div>
        <div className={styles['content']}>
            <ExternalLink
                href="#" //todo: add link
                onClick={handleClick}
            >
                {__('Read documentation on what we changed', 'give')}
            </ExternalLink>
        </div>
    </div>
)

