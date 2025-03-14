import {useEffect, useCallback} from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import LoadingOverlay from '@givewp/components/LoadingOverlay';

import styles from './styles.module.scss';

import {__} from '@wordpress/i18n';

const Modal = ({visible = true, type = 'notice', children = {}, isLoading = false, handleClose = () => {}, ...rest}) => {
    const closeModal = useCallback((event) => {
        if (event.keyCode === 27 && typeof handleClose === 'function') {
            handleClose();
        }
    }, []);

    useEffect(() => {
        document.addEventListener('keydown', closeModal, false);

        return () => {
            document.removeEventListener('keydown', closeModal, false);
        };
    }, []);

    const handleOverlayClick = (e) => {
        if (e.target.classList.contains(styles.overlay) && typeof handleClose === 'function') {
            handleClose();
        }
    };

    const modalStyles = classNames({
        [styles.modal]: true,
        [styles.error]: type === 'error' || type === 'failed',
        [styles.warning]: type === 'warning',
        [styles.success]: type === 'success',
        [styles.http]: type === 'http',
    });

    return (
        <div className={classNames({[styles.overlay]: visible})} onClick={handleOverlayClick} {...rest}>
            <div className={styles.container}>
                <div className={modalStyles}>
                    {isLoading && <LoadingOverlay spinnerSize="small" />}
                    <div className={styles.content}>{children}</div>
                </div>
            </div>
        </div>
    );
};

Modal.Title = ({children, ...rest}) => {
    return (
        <div className={styles.title} {...rest}>
            {children}
        </div>
    );
};

Modal.CloseIcon = ({onClick, ...rest}) => {
    return (
        <div className={styles.closeIconContainer}>
            <div className={styles.close} onClick={onClick} {...rest}>
                <span className="dashicons dashicons-no" />
            </div>
        </div>
    );
};

Modal.Section = ({title, content, ...rest}) => {
    return (
        <div className={styles.section} {...rest}>
            <strong>{title}:</strong>
            {content}
        </div>
    );
};

Modal.Content = ({children, align, ...rest}) => {
    const contentClasses = classNames({
        [styles.innerContent]: true,
        [styles.textCenter]: align === 'center',
        [styles.textRight]: align === 'right',
        [styles.textLeft]: !align || align === 'left',
    });

    return (
        <div className={contentClasses} {...rest}>
            {children}
        </div>
    );
};

Modal.AdditionalContext = ({type, context, ...rest}) => {
    const title = ['error', 'failed'].includes(type) ? __('Error details', 'give') : __('Additional context', 'give');

    return (
        <div className={styles.section} {...rest}>
            <strong>{title}:</strong>
            <div className={styles.errorDetailsContainer}>
                <pre>
                    {context instanceof Object
                        ? Object.entries(context).map(([key, value]) => {
                              return (
                                  <div key={key}>
                                      <span>{key}:</span>
                                      {value}
                                  </div>
                              );
                          })
                        : context}
                </pre>
            </div>
        </div>
    );
};

Modal.propTypes = {
    // Is visible
    visible: PropTypes.bool.isRequired,
    // Is loading
    isLoading: PropTypes.bool,
    // Modal type
    type: PropTypes.string,
    // Collection of react DOM elements
    children: PropTypes.arrayOf(PropTypes.object),
    // Handle close callback
    handleClose: PropTypes.func,
};

Modal.Title.propTypes = {
    // Collection of react DOM elements
    children: PropTypes.oneOfType([PropTypes.string, PropTypes.arrayOf(PropTypes.object)]),
};

Modal.CloseIcon.propTypes = {
    // On click callback
    onClick: PropTypes.func.isRequired,
};

Modal.Section.propTypes = {
    // Section title
    title: PropTypes.string.isRequired,
    // Section content
    content: PropTypes.string.isRequired,
};

Modal.Content.propTypes = {
    // Collection of react DOM elements
    children: PropTypes.object,
};

Modal.AdditionalContext.propTypes = {
    // Log type
    type: PropTypes.string.isRequired,
    // String or Array of objects
    context: PropTypes.any.isRequired,
};

export default Modal;
