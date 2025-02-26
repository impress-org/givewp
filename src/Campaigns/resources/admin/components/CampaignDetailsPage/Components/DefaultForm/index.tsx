import {__} from '@wordpress/i18n';
import HeaderText from '../HeaderText';
import HeaderSubText from '../HeaderSubText';

import styles from './styles.module.scss';

/**
 * @unreleased
 */
const DefaultFormWidget = ({defaultForm}: {defaultForm: string}) => {
    return (
        <div className={styles.defaultForm}>
            <div className={styles.description}>
                <div className={styles.headerSpacing}>
                    <HeaderText>{__('Default campaign form', 'give')}</HeaderText>
                    <HeaderSubText>{__('Your campaign page and blocks will collect donations through this form by default.', 'give')}</HeaderSubText>
                </div>
            </div>
            <div className={styles.formName}>
                {defaultForm}
            </div>
        </div>
    )
}

export default DefaultFormWidget;
