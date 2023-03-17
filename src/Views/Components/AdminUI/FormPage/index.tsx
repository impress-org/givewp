import React, {createContext, useRef, useState} from 'react';
import {createPortal} from 'react-dom';

import {__} from '@wordpress/i18n';
import {FormProvider, useForm} from 'react-hook-form';
import {joiResolver} from '@hookform/resolvers/joi';

import FormNavigation from '@givewp/components/AdminUI/FormNavigation';
import {Form} from '@givewp/components/AdminUI/FormElements';

import {FormPage} from '@givewp/components/AdminUI/types';
import cx from 'classnames';
import {A11yDialog} from 'react-a11y-dialog';
import Button from '@givewp/components/AdminUI/Button';
import A11yDialogInstance from 'a11y-dialog';

import styles from './style.module.scss';
import ExitIcon from '@givewp/components/AdminUI/Icons/ExitIcon';
import NoticeInformationIcon from '@givewp/components/AdminUI/Icons/NoticeInformationIcon';

/**
 *
 * @unreleased
 */

export const ModalContext = createContext((label, content, confirmationAction, exitCallback, button, notice) => {});

export default function FormPage({
    formId,
    handleSubmitRequest,
    defaultValues,
    validationSchema,
    pageDetails,
    navigationalOptions,
    children,
    actionConfig,
}: FormPage) {
    const dialog = useRef() as {current: A11yDialogInstance};
    const [modalContent, setModalContent] = useState<{
        label: string;
        content: () => JSX.Element;
        confirmationAction: () => void;
        exitCallback: () => void | null;
        button: string;
        notice: string | null;
    }>({
        label: '',
        content: null,
        confirmationAction: () => {},
        exitCallback: () => {},
        button: __('Save Changes', 'give'),
        notice: '',
    });

    const methods = useForm({
        defaultValues: defaultValues,
        resolver: joiResolver(validationSchema),
    });

    const {handleSubmit} = methods;

    const {isDirty} = methods.formState;

    const showConfirmActionModal = (label, content, confirmationAction, exitCallback, button, notice) => {
        setModalContent({label, content, confirmationAction, exitCallback, button, notice});
        dialog.current.show();
    };

    return (
        <FormProvider {...methods}>
            <ModalContext.Provider value={showConfirmActionModal}>
                <FormNavigation
                    pageId={pageDetails.id}
                    pageTitle={pageDetails.title}
                    pageDescription={pageDetails.description}
                    navigationalOptions={navigationalOptions}
                    onSubmit={handleSubmit(handleSubmitRequest)}
                    actionConfig={actionConfig}
                    isDirty={isDirty}
                />
                <Form id={formId} onSubmit={handleSubmit(handleSubmitRequest)}>
                    {children}
                </Form>
                {modalContent &&
                    createPortal(
                        <A11yDialog
                            id="givewp-admin-details-action-modal"
                            dialogRef={(instance) => (dialog.current = instance)}
                            title={modalContent.label}
                            classNames={{
                                container: styles.container,
                                overlay: styles.overlay,
                                dialog: cx(styles.dialog, {}),
                                closeButton: 'hidden',
                                title: 'hidden',
                            }}
                        >
                            <div className={styles.dialogTitle}>
                                <p aria-labelledby={modalContent.label}>{modalContent.label}</p>
                                <button
                                    onClick={(event) => {
                                        modalContent.exitCallback();
                                        dialog.current.hide();
                                    }}
                                >
                                    <ExitIcon />
                                </button>
                            </div>

                            <div className={styles.modalContentContainer}>{modalContent.content}</div>

                            <div className={styles.actionContainer}>
                                <Button
                                    onClick={() => {
                                        modalContent.confirmationAction();
                                        dialog.current.hide();
                                    }}
                                    disabled={!isDirty}
                                    variant={'primary'}
                                    size={'small'}
                                    type={'button'}
                                >
                                    {modalContent.button}
                                </Button>
                                <div className={styles.noticeInformation}>
                                    <NoticeInformationIcon />
                                    {modalContent.notice}
                                </div>
                            </div>
                        </A11yDialog>,
                        document.body
                    )}
            </ModalContext.Provider>
        </FormProvider>
    );
}
