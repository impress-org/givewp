import type {ReactNode} from 'react';
import cx from 'classnames';
import {FormToggle} from '@wordpress/components';
import {useInstanceId} from '@wordpress/compose';
import GiveMark from '../GiveMark';
import HeaderDivider from '../HeaderDivider';
import TestModeBadge from '../TestModeBadge';
import type {TestModeToggle} from './types';
import styles from './ListTablePage.module.scss';

/**
 * @since TBD Take the test-mode switch as `testModeToggle`; drop the stats props (the tiles render in the list sheet).
 */
export type ListTablePageHeaderProps = {
    title: string;
    /** The screen's header buttons. */
    children?: ReactNode;
    banner?: () => ReactNode;
    /** The test-mode switch's badge text and accessible name, on screens that have one. */
    testModeToggle?: TestModeToggle;
    testMode: boolean;
    onChangeTestMode: (isOn: boolean) => void;
};

/**
 * The Give part of a list screen above the list: title, header buttons, banner and test-mode toggle.
 *
 * @since TBD Take the test-mode switch as `testModeToggle` and show it in the header with the stock form toggle; the stat tiles move to the list sheet.
 */
export default function ListTablePageHeader({
    title,
    children,
    banner,
    testModeToggle,
    testMode,
    onChangeTestMode,
}: ListTablePageHeaderProps) {
    const switchId = `givewp-test-mode-switch-${useInstanceId(ListTablePageHeader)}`;

    return (
        <>
            <header className={styles.pageHeader}>
                <div className={styles.flexRow}>
                    <GiveMark />
                    <h1 className={styles.pageTitle}>{title}</h1>
                    {testModeToggle && testMode && <TestModeBadge label={testModeToggle.label} />}
                </div>
                {(children || testModeToggle) && (
                    <div className={styles.flexRow}>
                        {testModeToggle && (
                            <div className={styles.testModeSwitch}>
                                <FormToggle
                                    id={switchId}
                                    role="switch"
                                    checked={testMode}
                                    onChange={(event) => onChangeTestMode(event.target.checked)}
                                />
                                <label htmlFor={switchId}>{testModeToggle.ariaLabel || testModeToggle.label}</label>
                            </div>
                        )}
                        {testModeToggle && children && <HeaderDivider />}
                        {children}
                    </div>
                )}
            </header>

            <div className={cx('wp-header-end', 'hidden')} />

            {banner && <section role="banner">{banner()}</section>}
        </>
    );
}
