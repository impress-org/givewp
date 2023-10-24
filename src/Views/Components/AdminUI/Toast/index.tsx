import {MouseEventHandler, useEffect} from 'react';
import cx from 'classnames';
import {__} from '@wordpress/i18n';
import {ExitIcon, CheckCircle, AlertTriangle} from '@givewp/components/AdminUI/Icons';
import styles from './style.module.scss';

export interface ToastProps {
    children: string | JSX.Element | JSX.Element[];
    handleClose: MouseEventHandler;
    type?: 'info' | 'error' | 'warning' | 'success';
    show?: boolean;
    position?: 'top-right' | 'bottom-right' | 'top-left' | 'bottom-left';
    autoClose?: number;
    showCloseIcon?: boolean;
}

export default function Toast({children, type, handleClose, position = 'top-right', show = true, autoClose = 0, showCloseIcon = true}: ToastProps) {
    const getIcon = type => {
        switch (type) {
            case 'info':
                return <></>
            case 'error':
                return <></>
            case 'warning':
                return <AlertTriangle />
            case 'success':
                return <CheckCircle />
        }

        return null;
    }

    useEffect(() => {
        if(autoClose) {
            setTimeout(handleClose, autoClose)
        }
    }, []);

    if (!show) return null;

    return (
        <div className={cx(styles.toastContainer, {
            [styles.topLeft]: position === 'top-left',
            [styles.topRight]: position === 'top-right',
            [styles.bottomLeft]: position === 'bottom-left',
            [styles.bottomRight]: position === 'bottom-right',
            [styles.success]: type === 'success',
            [styles.warning]: type === 'warning',
            [styles.error]: type === 'error',
            [styles.info]: type === 'info',
        })}>
            {type && (
                <div className={styles.icon}>
                    {getIcon(type)}
                </div>
            )}
            <div className={styles.content}>
                {children}
            </div>
            {showCloseIcon && handleClose && (
                <div>
                    <button
                        aria-label={__('Close', 'give')}
                        className={styles.close}
                        onClick={handleClose}
                    >
                        <ExitIcon aria-label={__('Close icon', 'give')} className={styles.closeIconSize} />
                    </button>
                </div>
            )}
        </div>
    )
}

