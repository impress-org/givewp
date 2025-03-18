import {__} from '@wordpress/i18n';
import {ExternalLink} from '@wordpress/components';
import {CloseIcon} from '@givewp/campaigns/admin/components/Icons';

import styles from './styles.module.scss';

export default ({handleClick}) => (
    <div className={styles.tooltip} style={{
        position: 'absolute',
        left: 250,
        top: 150,
    }}>
        <div className={styles.close} onClick={handleClick}>
            <CloseIcon />
        </div>
        <h3>
            {__('Campaign Form', 'give')}
        </h3>
        <div className={styles.content}>
            {__('Get a quick view of all the forms associated with your campaign in the forms page. You can edit and add multiple forms to your campaign.', 'give')}
        </div>
        <div className={styles.content}>
            <ExternalLink
                href="#" //todo: add link
                onClick={handleClick}
            >
                {__('All you need to know about campaigns', 'give')}
            </ExternalLink>
        </div>
    </div>
)

