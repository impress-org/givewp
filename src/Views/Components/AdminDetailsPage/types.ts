import React, { FC } from 'react';

/**
 * Interface for controlling UI element visibility
 *
 * @since 4.4.0
 */
export interface Show {
    contextMenu?: boolean;
    confirmationModal?: boolean;
}

/**
 * Props for the AdminDetailsPage component
 *
 * @since 4.4.0
 */
export interface AdminDetailsPageProps<T extends Record<string, any>> {
    /**
     * ID of the object being displayed/edited
     */
    objectId: string | number;

    /**
     * Type of object (e.g., 'donor', 'campaign', etc.)
     */
    objectType: string;

    /**
     * Plural type of object (e.g., 'donors', 'campaigns', etc.)
     */
    objectTypePlural: string;

    /**
     * Hook that provides entity data and methods
     */
    useObjectEntityRecord: (id: string | number) => {
        record: T;
        hasResolved: boolean;
        save: () => any;
        edit: (data: T | Partial<T>) => void;
    };

    /**
     * Function to determine if the form should be saved
     */
    shouldSaveForm?: (isDirty: boolean, data: T) => boolean;

    /**
     * URL for the breadcrumb link
     */
    breadcrumbUrl: string;

    /**
     * Custom title for the breadcrumb (defaults to entity.name)
     */
    breadcrumbTitle?: string;

    /**
     * Custom title for the page header (defaults to entity.name)
     */
    pageTitle?: string | React.ReactNode;

    /**
     * Component to display the status badge
     */
    StatusBadge?: React.ComponentType;

    /**
     * Component to display the primary action button
     */
    PrimaryActionButton?: React.ComponentType<{ isSaving: boolean, formState: any, className: string }>;

    /**
     * Component to display the secondary action button
     */
    SecondaryActionButton?: React.ComponentType<{ className: string }>;

    /**
     * Component to display the context menu items
     */
    ContextMenuItems?: React.ComponentType<{ className: string }>;

    /**
     * Tabs definition for the object
     */
    tabDefinitions: Tab[];

    /**
     * Component to display the children
     */
    children?: React.ReactNode;
}

/**
 * @since 4.4.0
 */
export type Tab = {
    id: string;
    title: string;
} & (
        | { link: string; content?: never; fullwidth?: boolean }
        | { link?: never; content: FC; fullwidth?: boolean }
    );

/**
 * @since 4.4.0
 */
export type Notification = {
    id: string;
    content: string | JSX.Element | Function;
    notificationType?: 'notice' | 'snackbar';
    type?: 'error' | 'warning' | 'info' | 'success';
    isDismissible?: boolean;
    autoHide?: boolean;
    onDismiss?: () => void;
    duration?: number,
}

/**
 * @since 4.4.0
 */
declare module "@wordpress/data" {
    export function select(key: 'givewp/admin-details-page-notifications'): {
        getNotifications(): Notification[],
        getNotificationsByType(type: 'snackbar' | 'notice'): Notification[]
    };

    export function dispatch(key: 'givewp/admin-details-page-notifications'): {
        addSnackbarNotice(notification: Notification): void,
        addNotice(notification: Notification): void,
        dismissNotification(id: string): void
    };
}
