import {Button, Icon, Modal as GutenbergModal} from '@wordpress/components';
import {info, warning} from '@wordpress/icons';
import GutenbergModalTypes from 'wordpress__components/Modal';
import cx from 'classnames';
import './styles.scss';

interface ModalProps extends GutenbergModalTypes.Props {
    closeButtonCaption?: string;
}

export function Modal({onRequestClose, closeButtonCaption, children, ...props}: ModalProps) {
    return (
        // @ts-ignore
        <GutenbergModal className="give-modal" onRequestClose={onRequestClose} {...props}>
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

export function InfoModal({
    type = ModalType.Info,
    onRequestClose,
    children,
    ...props
}: InfoModalProps) {
    const classes = cx('give-modal', 'give-modal--has-icon', {
        'give-modal--warning': type === ModalType.Warning,
        'give-modal--error': type === ModalType.Error,
    });

    const icon = icons[type];

    return (
        // @ts-ignore
        <GutenbergModal icon={<Icon icon={icon} />} className={classes} onRequestClose={onRequestClose} {...props}>
            {children}
        </GutenbergModal>
    );
}
