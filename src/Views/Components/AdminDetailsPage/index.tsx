/**
 * External Dependencies
 */
import {useEffect, useState, useRef} from 'react';
import {JSONSchemaType} from 'ajv';
import {ajvResolver} from '@hookform/resolvers/ajv';
import {FormProvider, SubmitHandler, useForm, useFormContext, useFormState} from 'react-hook-form';

/**
 * WordPress Dependencies
 */
import {__} from '@wordpress/i18n';
import {useDispatch} from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { SlotFillProvider } from '@wordpress/components';
import { PluginArea } from '@wordpress/plugins';

/**
 * Internal Dependencies
 */
import {Spinner as GiveSpinner} from '@givewp/components';
import TabsRouter from './Tabs/Router';
import TabList from './Tabs/TabList';
import {BreadcrumbSeparatorIcon, DotsIcons} from './Icons';
import NotificationPlaceholder from './Notifications';
import {AdminDetailsPageProps} from './types';
import styles from './AdminDetailsPage.module.scss';
import ErrorBoundary from './ErrorBoundary';
import TabPanels from './Tabs/TabPanels';
import DefaultPrimaryActionButton from './DefaultPrimaryActionButton';
import AdminSection, { AdminSectionField, AdminSectionsWrapper } from './AdminSection';

import './store';

/**
 * @since 4.4.0
 */
export default function AdminDetailsPage<T extends Record<string, any>>({
    objectId,
    objectType,
    objectTypePlural,
    useObjectEntityRecord,
    resetForm,
    shouldSaveForm,
    breadcrumbUrl,
    breadcrumbTitle,
    pageTitle,
    StatusBadge,
    PrimaryActionButton = DefaultPrimaryActionButton,
    SecondaryActionButton,
    ContextMenuItems,
    tabDefinitions,
    children,
}: AdminDetailsPageProps<T>) {
    const [resolver, setResolver] = useState({});
    const [isSaving, setIsSaving] = useState(false);
    const [showContextMenu, setShowContextMenu] = useState<boolean>(false);
    const contextMenuButtonRef = useRef<HTMLButtonElement>(null);
    const contextMenuRef = useRef<HTMLDivElement>(null);

    const dispatch = useDispatch(`givewp/admin-details-page-notifications`);

    exposeAdminComponentsAndHooks();

    useEffect(() => {
        if (!objectId) {
            return;
        }

        apiFetch({
            path: `/givewp/v3/${objectTypePlural}/${objectId}`,
            method: 'OPTIONS',
        }).then(({schema}: {schema: JSONSchemaType<any>}) => {
            setResolver({
                resolver: ajvResolver(schema),
            });
        });
    }, [objectId]);

    const {record, hasResolved, save, edit} = useObjectEntityRecord(objectId);

    const methods = useForm<T>({
        mode: 'onBlur',
        shouldFocusError: true,
        ...resolver,
    });

    const {formState, handleSubmit, reset} = methods;

    // Close context menu when clicked outside
    useEffect(() => {
        const handleClickOutside = (e: MouseEvent) => {
            if (!showContextMenu) {
                return;
            }

            if (
                e.target instanceof HTMLElement &&
                !contextMenuButtonRef.current?.contains(e.target) &&
                !contextMenuRef.current?.contains(e.target)
            ) {
                setShowContextMenu(false);
                contextMenuButtonRef.current?.blur();
            }
        };

        document.addEventListener('click', handleClickOutside);

        return () => {
            document.removeEventListener('click', handleClickOutside);
        };
    }, [showContextMenu]);

    // Set default values when entity is loaded
    useEffect(() => {
        if (hasResolved) {
            if (resetForm) {
                resetForm(reset);
            } else {
                reset(record);
            }
        }
    }, [hasResolved]);

    const onSubmit: SubmitHandler<T> = async (data) => {
        const shouldSave = shouldSaveForm ? shouldSaveForm(formState.isDirty, data) : formState.isDirty;

        if (shouldSave) {
            setIsSaving(true);

            edit(data);

            try {
                const response = await save();

                setIsSaving(false);
                reset(response);

                dispatch.addSnackbarNotice({
                    id: `save-success`,
                    content: __(`${objectType.charAt(0).toUpperCase() + objectType.slice(1)} updated`, 'give'),
                });
            } catch (err) {
                console.error(err);
                setIsSaving(false);

                dispatch.addSnackbarNotice({
                    id: `save-error`,
                    type: 'error',
                    content: __(`${objectType.charAt(0).toUpperCase() + objectType.slice(1)} update failed`, 'give'),
                });
            }
        }
    };

    if (!hasResolved) {
        return (
            <div className={styles.loadingContainer}>
                <div className={styles.loadingContainerContent}>
                    <GiveSpinner />
                    <div className={styles.loadingContainerContentText}>{__(`Loading ${objectType}...`, 'give')}</div>
                </div>
            </div>
        );
    }

    return (
        <ErrorBoundary>
            <FormProvider {...methods}>
                <SlotFillProvider>
                    <form id={'givewp-details-form'} onSubmit={handleSubmit(onSubmit)}>
                        <article className={`interface-interface-skeleton__content ${styles.page}`}>
                            <TabsRouter tabDefinitions={tabDefinitions}>
                                <header className={styles.pageHeader}>
                                    <div className={styles.breadcrumb}>
                                        <a href={breadcrumbUrl}>{objectTypePlural.charAt(0).toUpperCase() + objectTypePlural.slice(1)}</a>
                                        <BreadcrumbSeparatorIcon />
                                        <span>{breadcrumbTitle || record?.name}</span>
                                    </div>
                                    <div className={styles.flexContainer}>
                                        <div className={styles.flexRow}>
                                            <h1 className={styles.pageTitle}>{pageTitle || record?.name}</h1>
                                            {StatusBadge && <StatusBadge />}
                                        </div>

                                        <div className={`${styles.flexRow} ${styles.justifyContentEnd}`}>
                                            {SecondaryActionButton && (
                                                <SecondaryActionButton
                                                    className={`button button-tertiary ${styles.secondaryActionButton}`}
                                                />
                                            )}

                                            <PrimaryActionButton
                                                isSaving={isSaving}
                                                formState={formState}
                                                className={`button button-primary ${styles.primaryActionButton}`}
                                            />

                                            {ContextMenuItems && (
                                                <>
                                                    <button
                                                        ref={contextMenuButtonRef}
                                                        className={`button button-secondary ${styles.contextMenuButton}`}
                                                        onClick={(e) => {
                                                            e.preventDefault();
                                                            setShowContextMenu(!showContextMenu);
                                                        }}
                                                    >
                                                        <DotsIcons />
                                                    </button>

                                                    {!isSaving && showContextMenu && (
                                                        <div ref={contextMenuRef} className={styles.contextMenu}>
                                                            <ContextMenuItems className={styles.contextMenuItem} />
                                                        </div>
                                                    )}
                                                </>
                                            )}
                                        </div>
                                    </div>
                                    <TabList tabDefinitions={tabDefinitions} />
                                </header>

                                <TabPanels tabDefinitions={tabDefinitions} />

                                {children}
                            </TabsRouter>
                        </article>
                    </form>

                    <NotificationPlaceholder type="snackbar" />

                    <PluginArea scope={`givewp-${objectType}-details-page`} />
                </SlotFillProvider>
            </FormProvider>
        </ErrorBoundary>
    );
}

const exposeAdminComponentsAndHooks = (): void => {
    (window as any).givewp = (window as any).givewp || {};
    (window as any).givewp.admin = (window as any).givewp.admin || {};
    (window as any).givewp.admin.components = (window as any).givewp.admin.components || {};
    (window as any).givewp.admin.hooks = (window as any).givewp.admin.hooks || {};

    Object.assign((window as any).givewp.admin, {
        components: {
            AdminSection,
            AdminSectionField,
        },
        hooks: {
            useFormContext,
            useFormState,
        }
    });
}
