import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import ErrorMessages from './ErrorMessages';
import styles from './FormModal.module.scss';

export default function FormModal({
    isOpen,
    handleClose,
    title,
    handleSubmit,
    errors,
    errorMessages,
    className,
    children,
}: FormModalProps) {
    return (
        <ModalDialog
            isOpen={isOpen}
            showHeader={true}
            handleClose={handleClose}
            title={title}
            wrapperClassName={styles.formModal}
        >
            <form className={`givewp-event-tickets__form ${className}`} onSubmit={handleSubmit}>
                <ErrorMessages errors={errors} errorMessages={errorMessages} />

                {children}
            </form>
        </ModalDialog>
    );
}

interface FormModalProps {
    isOpen: boolean;
    handleClose: () => void;
    title: string;
    handleSubmit: (e: React.FormEvent<HTMLFormElement>) => void;
    errors: Record<string, any>;
    errorMessages: { [key: string]: string };
    className: string;
    children: JSX.Element | JSX.Element[];
}
