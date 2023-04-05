import cx from 'classnames';

import DownArrowIcon from '@givewp/components/AdminUI/Icons/DownArrowIcon';
import ExternalIcon from '@givewp/components/AdminUI/Icons/ExternalIcon';

import styles from './style.module.scss';
import AddIcon from '@givewp/components/AdminUI/Icons/AddIcon';

/**
 *
 * @unreleased
 */
export type SectionHeaderProps = {
    children: React.ReactNode;
};

export default function SectionHeader({children}: SectionHeaderProps) {
    return <header className={styles.formSectionHeader}>{children}</header>;
}

/**
 *
 * @unreleased
 */

export type DropdownTitleProps = {
    isOpen: boolean;
    title: string;
    handleDropdown: () => void;
};

export function DropdownTitle({isOpen, title, handleDropdown}: DropdownTitleProps) {
    return (
        <button type={'button'} className={styles.formSectionHeaderTitleContainer} onClick={handleDropdown}>
            <span
                className={cx(styles.spinContainer, {
                    [styles.spin]: !isOpen,
                })}
            >
                <DownArrowIcon color={'#0e0e0e'} />
            </span>
            <h3>{title}</h3>
        </button>
    );
}

/**
 *
 * @unreleased
 */
export type TitleProps = {
    title: string;
};

export function Title({title}: TitleProps) {
    return <h3>{title}</h3>;
}

/**
 *
 * @unreleased
 */

export type HeaderLinkProps = {
    href: string;
    children: React.ReactNode;
};

export function HeaderLink({href, children}: HeaderLinkProps) {
    return (
        <a className={styles.headerLink} href={href}>
            <ExternalIcon />
            {children}
        </a>
    );
}

/**
 *
 * @unreleased
 */

export type HeaderActionProps = {
    action: () => void;
    children: React.ReactNode;
};

export function HeaderAction({children, action}: HeaderActionProps) {
    return (
        <button className={styles.headerAction} type={'button'} onClick={action}>
            <AddIcon />
            {children}
        </button>
    );
}
