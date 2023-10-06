import {Button, Icon, Modal as GutenbergModal} from '@wordpress/components';
import type GutenbergModalProps from './types'
import {info, warning} from '@wordpress/icons';
import cx from 'classnames';
import './styles.scss';

interface ModalProps extends GutenbergModalProps {
    closeButtonCaption?: string;
}

export function Modal({onRequestClose, closeButtonCaption, className, children, ...props}: ModalProps) {
    return (
        // @ts-ignore
        <GutenbergModal className={cx('give-modal', className)} onRequestClose={onRequestClose} {...props}>
            {children}
            {closeButtonCaption && (
                // @ts-ignore
                <Button variant="primary" className="give-modal__dismiss-button" onClick={onRequestClose}>
                    {closeButtonCaption}
                </Button>
            )}
        </GutenbergModal>
    );
}

interface InfoModalProps extends ModalProps {
    type?: ModalType;
}

export enum ModalType {
    Info = 'info',
    Warning = 'warning',
    Error = 'error',
}

const icons = {
    [ModalType.Info]: info,
    [ModalType.Warning]: warning,
    [ModalType.Error]: warning,
};

export function InfoModal({type = ModalType.Info, onRequestClose, children, ...props}: InfoModalProps) {
    const classes = cx('give-modal--has-icon', {
        'give-modal--warning': type === ModalType.Warning,
        'give-modal--error': type === ModalType.Error,
    });

    const icon = icons[type];

    return (
        // @ts-ignore
        <Modal icon={<Icon icon={icon} />} className={classes} onRequestClose={onRequestClose} {...props}>
            {children}
        </Modal>
    );
}
