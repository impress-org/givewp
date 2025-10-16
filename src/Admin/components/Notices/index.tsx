import {CloseIcon, ErrorIcon, InfoIcon, WarningIcon} from '@givewp/admin/components/Notices/Icons';
import {useState} from '@wordpress/element';
import {__} from '@wordpress/i18n';
import styles from './styles.module.scss';

/**
 * @since 4.10.0 Add className prop
 * @since 4.8.0
 */
interface Props {
    type: 'info' | 'warning' | 'error';
    className?: string;
    children: React.ReactNode;
    dismissHandleClick?: () => void;
}

/**
 * @since 4.10.0 Add className prop
 * @since 4.8.0
 */
export default ({type, children, dismissHandleClick, className}: Props) => {
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

    const noticeClasses = `${styles.notice} ${className} ${
        type === 'warning' ? styles.warning : type === 'error' ? styles.error : styles.info
    }`;

    const NoticeIcon = ({type}: {type: Props['type']}) => {
        const icons = {
            warning: WarningIcon,
            error: ErrorIcon,
            info: InfoIcon,
        };
        const IconComponent = icons[type] ?? icons.info;
        return <IconComponent />;
    };

    return (
        <div className={noticeClasses}>
            <NoticeIcon type={type} />
            <div className={styles.content}>{children}</div>
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
