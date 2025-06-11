/**
 * External Dependencies
 */
import React, { ReactNode } from 'react';

/**
 * Internal Dependencies
 */
import styles from './styles.module.scss';

/**
 * AdminPanelWrapper - Flexible wrapper for overview/panel layout
 * Supports top section, 2-column middle, and bottom section
 * 
 * @unreleased
 */
export default function AdminPanelWrapper({ 
    above,
    leftColumn = [], 
    rightColumn = [],
    below
}: { 
    above?: ReactNode;
    leftColumn?: ReactNode[];
    rightColumn?: ReactNode[];
    below?: ReactNode;
}) {
    return (
        <div className={styles.container}>
            {above && (
                <div className={styles.aboveSection}>
                    {above}
                </div>
            )}
            
            <div className={styles.grid}>
                <div className={styles.leftColumn}>
                    {leftColumn.map((child, index) => (
                        <div key={index}>
                            {child}
                        </div>
                    ))}
                </div>
                
                <div className={styles.rightColumn}>
                    {rightColumn.map((child, index) => (
                        <div key={index}>
                            {child}
                        </div>
                    ))}
                </div>
            </div>
            
            {below && (
                <div className={styles.belowSection}>
                    {below}
                </div>
            )}
        </div>
    );
} 