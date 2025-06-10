import React from 'react';
import styles from './styles.module.scss';

/**
 * @unreleased
 */
type HeaderProps = {
    children?: React.ReactNode;
    title: string;
    subtitle?: string;
    href?: string;
    actionText?: string;
    actionOnClick?: () => void;
};

/**
 * @unreleased
 */
export default function Header({title, subtitle, href, actionText, actionOnClick}: HeaderProps) {
    return (
        <header className={styles.header}>
            <div>
                <HeaderText>{title}</HeaderText>
                {subtitle && <SubHeaderText>{subtitle}</SubHeaderText>}
            </div>
            {href && !actionOnClick && (
                <a className={styles.action} href={href} rel={'noreferrer'} aria-label={`${actionText} for ${title}`}>
                    {actionText}
                </a>
            )}
            {actionOnClick && !href && (
                <button className={styles.action} onClick={actionOnClick} aria-label={`${actionText} for ${title}`}>
                    {actionText}
                </button>
            )}
        </header>
    );
}

/**
 * @unreleased
 */
export function HeaderText({children}: {children: React.ReactNode}) {
    return <h2 className={styles.headerText}>{children}</h2>;
}

/**
 * @unreleased
 */
export function SubHeaderText({children}: {children: React.ReactNode}) {
    return <p className={styles.subHeaderText}>{children}</p>;
}
