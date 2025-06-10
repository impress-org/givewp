import cx from 'classnames';
import {ExternalLink} from '@wordpress/components';
import {CloseIcon} from '@givewp/campaigns/admin/components/Icons';

import styles from './Notices.module.scss';

interface NoticeProps {
    title: string;
    description: string;
    handleDismiss?: () => void
    type: 'campaignSettings' | 'campaignList' | 'campaignForm';
    linkText?: string;
    linkHref?: string;
}

export default ({title, description, type, linkHref, linkText, handleDismiss}: NoticeProps) => (
    <div className={cx(styles['tooltip'], styles[type])}>
        {handleDismiss && (
            <div className={styles['close']} onClick={() => handleDismiss()}>
                <CloseIcon />
            </div>
        )}
        <div>
        <h3>
            {title}
        </h3>
        <div className={styles['content']}>
            {description}
        </div>
        </div>
        {linkText && (
            <div className={styles['content']}>
                <ExternalLink href={linkHref}>
                    {linkText}
                </ExternalLink>
            </div>
        )}
    </div>
)

