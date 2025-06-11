/**
 * External Dependencies
 */
import React, { ReactNode } from 'react';

/**
 * Internal Dependencies
 */
import ErrorBoundary from './ErrorBoundary';
import NotificationPlaceholder from './Notifications';
import styles from './AdminPanelWrapper.module.scss';

/**
 * AdminPanelWrapper - Simple 2-column wrapper for overview/panel layout
 * Takes left and right column components for clear, predictable placement
 * 
 * @unreleased
 */
export default function AdminPanelWrapper({ 
    leftColumn = [], 
    rightColumn = [] 
}: { 
    leftColumn?: ReactNode[];
    rightColumn?: ReactNode[];
}) {
    return (
        <ErrorBoundary>
            <div className={styles.grid}>
                <div className={styles.leftColumn}>
                    {leftColumn.map((child, index) => (
                        <ErrorBoundary key={index}>
                            {child}
                        </ErrorBoundary>
                    ))}
                </div>
                
                <div className={styles.rightColumn}>
                    {rightColumn.map((child, index) => (
                        <ErrorBoundary key={index}>
                            {child}
                        </ErrorBoundary>
                    ))}
                </div>
                
                <NotificationPlaceholder type="snackbar" />
            </div>
        </ErrorBoundary>
    );
} 