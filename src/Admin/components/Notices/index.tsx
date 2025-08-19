import {useState} from '@wordpress/element';
import {__} from '@wordpress/i18n';
import {WarningIcon, ErrorIcon, InfoIcon, CloseIcon} from '@givewp/admin/components/Notices/Icons';
import styles from './styles.module.scss';

/**
 * @unreleased
 */
interface Props {
    type: 'info' | 'warning' | 'error';
    children: React.ReactNode;
    dismissHandleClick?: () => void;
}

/**
 * @unreleased
 */
export default ({type, children, dismissHandleClick}: Props) => {
    const [isVisible, setIsVisible] = useState(true);
    
    const handleDismiss = () => {
        setIsVisible(false);
        if (dismissHandleClick) {
            dismissHandleClick();
        }
    };
    
    if (!isVisible) {
        return null;
    }
    
    const noticeClasses = `${styles.notice} ${
        type === 'warning' ? styles.warning : type === 'error' ? styles.error : styles.info
    }`;
    
    return (
        <div className={noticeClasses}>
            {type === 'warning' && <WarningIcon />}
            {type === 'error' && <ErrorIcon />}
            {type === 'info' && <InfoIcon />}
            <div className={styles.content}>
                {children}
            </div>
            {dismissHandleClick && (
                <button
                    type="button"
                    className={styles.dismissButton}
                    onClick={handleDismiss}
                    aria-label={__('Dismiss notice', 'give')}
                >
                    <CloseIcon />
                </button>
            )}
        </div>
    );
};
