/**
 * External Dependencies
 */
import React, { ReactNode } from 'react';

/**
 * Internal Dependencies
 */
import ErrorBoundary from './ErrorBoundary';
import NotificationPlaceholder from './Notifications';
import styles from './AdminOverviewWrapper.module.scss';

/**
 * AdminOverviewWrapper - Simple wrapper that handles overview grid layout
 * Similar to AdminSectionsWrapper but for overview pages with top/left/right layout
 * 
 * @unreleased
 */
export default function AdminOverviewWrapper({ children }: { children: ReactNode }) {
    const childrenArray = React.Children.toArray(children);
    
    // First child goes to top section (full width)
    // Remaining children split between left column (all but last) and right column (last)
    const [topChild, ...remainingChildren] = childrenArray;
    const leftColumnChildren = remainingChildren.length > 1 ? remainingChildren.slice(0, -1) : remainingChildren;
    const rightColumnChildren = remainingChildren.length > 1 ? remainingChildren.slice(-1) : [];
    
    return (
        <ErrorBoundary>
            <div className={styles.grid}>
                {topChild && (
                    <div className={styles.topSection}>
                        <ErrorBoundary>
                            {topChild}
                        </ErrorBoundary>
                    </div>
                )}
                
                <div className={styles.leftColumn}>
                    {leftColumnChildren.map((child, index) => (
                        <ErrorBoundary key={index}>
                            {child}
                        </ErrorBoundary>
                    ))}
                </div>
                
                <div className={styles.rightColumn}>
                    {rightColumnChildren.map((child, index) => (
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