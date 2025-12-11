import React from 'react';
import styles from './styles.module.scss';

/**
 * @since 4.5.0
 */
interface OverviewPanelProps {
    children: React.ReactNode;
    className?: string;
}

/**
 * @since 4.5.0
 */
export default function OverviewPanel({ children, className = '' }: OverviewPanelProps) {
    return (
        <div className={`${styles.panel} ${className}`}>
            {children}
        </div>
    );
}
