import React from 'react';
import styles from './styles.module.scss';

/**
 * @unreleased
 */
interface OverviewPanelProps {
    children: React.ReactNode;
    className?: string;
}

/**
 * @unreleased
 */
export default function OverviewPanel({ children, className = '' }: OverviewPanelProps) {
    return (
        <div className={`${styles.panel} ${className}`}>
            {children}
        </div>
    );
}
