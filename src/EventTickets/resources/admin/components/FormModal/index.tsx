import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import ErrorMessages from './ErrorMessages';
import styles from './FormModal.module.scss';

/**
 * Form Modal component that renders a modal with a styled form inside
 *
 * @since 3.6.0
 */
export default function FormModal({
    isOpen,
    handleClose,
    title,
    handleSubmit,
    errors,
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
                <ErrorMessages errors={errors} />

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
    className: string;
    children: JSX.Element | JSX.Element[];
}
