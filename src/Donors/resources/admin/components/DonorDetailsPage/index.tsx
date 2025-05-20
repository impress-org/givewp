import {__} from '@wordpress/i18n';
import {useEffect, useState} from '@wordpress/element';
import {useDispatch} from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import {JSONSchemaType} from 'ajv';
import {ajvResolver} from '@hookform/resolvers/ajv';
import {Donor} from '../types';
import {FormProvider, SubmitHandler, useForm} from 'react-hook-form';
import {Spinner as GiveSpinner} from '@givewp/components';
import {Spinner} from '@wordpress/components';
import Tabs from './Tabs';
import {BreadcrumbSeparatorIcon, DotsIcons, TrashIcon, ViewIcon} from '../Icons';
import NotificationPlaceholder from '../Notifications';
import cx from 'classnames';
import {getDonorOptionsWindowData, useDonorEntityRecord} from '@givewp/donors/utils';

import styles from './DonorDetailsPage.module.scss';
import DonorDetailsErrorBoundary from './Components/DonorDetailsErrorBoundary';

interface Show {
    contextMenu?: boolean;
    confirmationModal?: boolean;
}

const StatusBadge = ({status}: {status: string}) => {
    const statusMap = {
        current: __('Current', 'give'),
        prospective: __('Prospective', 'give'),
        retained: __('Retained', 'give'),
        lapsed: __('Lapsed', 'give'),
        new: __('New', 'give'),
        recaptured: __('Recaptured', 'give'),
        recurring: __('Recurring', 'give'),
    };

    if (!statusMap[status]) {
        return null;
    }

    return (
        <div className="interweave">
            <div className={`statusBadge statusBadge--${status}`}>
                <p>{statusMap[status]}</p>
            </div>
        </div>
    );
};

export default function DonorDetailsPage({donorId}) {
    const {adminUrl} = getDonorOptionsWindowData();
    const [resolver, setResolver] = useState({});
    const [isSaving, setIsSaving] = useState(false);
    const [show, _setShowValue] = useState<Show>({
        contextMenu: false,
        confirmationModal: false,
    });

    const dispatch = useDispatch('givewp/donor-notifications');

    const setShow = (data: Show) => {
        _setShowValue((prevState) => {
            return {
                ...prevState,
                ...data,
            };
        });
    };

    useEffect(() => {
        apiFetch({
            path: `/givewp/v3/donors/${donorId}`,
            method: 'OPTIONS',
        }).then(({schema}: {schema: JSONSchemaType<any>}) => {
            setResolver({
                resolver: ajvResolver(schema),
            });
        });
    }, []);

    const {donor, hasResolved, save, edit} = useDonorEntityRecord(donorId);

    const methods = useForm<Donor>({
        mode: 'onBlur',
        shouldFocusError: true,
        ...resolver,
    });

    const {formState, handleSubmit, reset, setValue} = methods;

    // Close context menu when clicked outside
    useEffect(() => {
        document.addEventListener('click', (e) => {
            if (show.contextMenu) {
                return;
            }

            if (
                e.target instanceof HTMLElement &&
                !e.target.closest(`.${styles.donorButtonDots}`) &&
                !e.target.closest(`.${styles.contextMenu}`)
            ) {
                setShow({contextMenu: false});
                (document.querySelector(`.${styles.donorButtonDots}`) as HTMLElement)?.blur();
            }
        });
    }, []);

    // Set default values when donor is loaded
    useEffect(() => {
        if (hasResolved) {
            const {userId, ...rest} = donor;
            // exclude userId from default values if null
            if (userId > 0) {
                reset({...donor, userId});
            } else {
                reset({...rest});
            }
        }
    }, [hasResolved]);

    const onSubmit: SubmitHandler<Donor> = async (data) => {
        const shouldSave = formState.isDirty;

        if (shouldSave) {
            setIsSaving(true);

            edit(data);

            try {
                const response = await save();

                setIsSaving(false);
                reset(response);

                dispatch.addSnackbarNotice({
                    id: `save-success`,
                    content: __('Donor updated', 'give'),
                });
            } catch (err) {
                console.error(err);
                setIsSaving(false);

                dispatch.addSnackbarNotice({
                    id: `save-error`,
                    type: 'error',
                    content: __('Donor update failed', 'give'),
                });
            }
        }
    };

    if (!hasResolved) {
        return (
            <div className={styles.loadingContainer}>
                <div className={styles.loadingContainerContent}>
                    <GiveSpinner />
                    <div className={styles.loadingContainerContentText}>{__('Loading donor...', 'give')}</div>
                </div>
            </div>
        );
    }

    return (
        <DonorDetailsErrorBoundary>
            <FormProvider {...methods}>
                <form onSubmit={handleSubmit(onSubmit)}>
                    <article className={`interface-interface-skeleton__content ${styles.page}`}>
                        <header className={styles.pageHeader}>
                            <div className={styles.breadcrumb}>
                                <a href={`${adminUrl}edit.php?post_type=give_forms&page=give-donors`}>
                                    {__('Donors', 'give')}
                                </a>
                                <BreadcrumbSeparatorIcon />
                                <span>{donor.name}</span>
                            </div>
                            <div className={styles.flexContainer}>
                                <div className={styles.flexRow}>
                                    <h1 className={styles.pageTitle}>{donor.name}</h1>
                                    <StatusBadge status={donor.status} />
                                </div>

                                <div className={`${styles.flexRow} ${styles.justifyContentEnd}`}>
                                    <button
                                        type="button"
                                        className={`button button-tertiary ${styles.sendDonorEmailButton}`}
                                        onClick={() => {}} // TODO: Add email sending logic
                                    >
                                        {__('Send Email', 'give')}
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={!formState.isDirty}
                                        className={`button button-primary ${styles.updateDonorButton}`}
                                    >
                                        {isSaving ? (
                                            <>
                                                {__('Saving changes', 'give')}
                                                <Spinner />
                                            </>
                                        ) : (
                                            __('Save changes', 'give')
                                        )}
                                    </button>

                                    <button
                                        className={`button button-secondary ${styles.donorButtonDots}`}
                                        onClick={(e) => {
                                            e.preventDefault();
                                            setShow({contextMenu: !show.contextMenu});
                                        }}
                                    >
                                        <DotsIcons />
                                    </button>

                                    {!isSaving && show.contextMenu && (
                                        <div className={styles.contextMenu}>
                                            {donor.wpUserPermalink && (
                                                <a
                                                    href={donor.wpUserPermalink}
                                                    aria-label={__('View WordPress profile', 'give')}
                                                    className={styles.contextMenuItem}
                                                >
                                                    <ViewIcon /> {__('View WordPress profile', 'give')}
                                                </a>
                                            )}
                                            <a
                                                href="#"
                                                className={cx(styles.contextMenuItem, styles.archive)}
                                                onClick={() => setShow({confirmationModal: true})}
                                            >
                                                <TrashIcon /> {__('Delete Donor', 'give')}
                                            </a>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </header>
                        <Tabs />
                    </article>
                </form>

                <NotificationPlaceholder type="snackbar" />
            </FormProvider>
        </DonorDetailsErrorBoundary>
    );
}
