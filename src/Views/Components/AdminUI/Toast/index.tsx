import {Fragment, useEffect, useState} from 'react';

import cx from 'classnames';
import CircularExitIcon from '@givewp/components/AdminUI/Icons/CircularExitIcon';

import styles from './style.module.scss';

export type ToastProps = {
    resultType: 'success' | 'error' | null;
    resultMessage: string;
    closeMessage: () => void;
    openMessage: () => void;
    showMessage: boolean;
};

export default function Toast({resultType, resultMessage, openMessage, showMessage, closeMessage}: ToastProps) {
    const [isActive, setIsActive] = useState(false);

    useEffect(() => {
        if (showMessage) {
            // Wait for the next frame to allow the .slide-in class to take effect
            requestAnimationFrame(() => {
                setIsActive(true);
            });
        } else {
            setTimeout(() => setIsActive(false), 500);
        }
    }, [showMessage]);

    return (
        <Fragment>
            {isActive && (
                <div
                    className={cx(styles.apiResult, styles[resultType], {
                        [styles.animateIn]: showMessage,
                        [styles.animateOut]: !showMessage,
                    })}
                >
                    {resultMessage}
                    <button onClick={closeMessage}>
                        <CircularExitIcon color={resultType === 'success' ? '#08a657' : '#a62308'} />
                    </button>
                </div>
            )}
        </Fragment>
    );
}
