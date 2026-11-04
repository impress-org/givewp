import type {ReactNode} from 'react';
import cx from 'classnames';
import {GiveIcon} from '@givewp/components';
import ToggleSwitch from '@givewp/components/ListTable/ToggleSwitch';
import ListTableStats, {StatConfig} from '../ListTableStats/ListTableStats';
import type {TestModeToggle} from './types';
import styles from './ListTablePage.module.scss';

/**
 * @since TBD Take the test-mode switch as `testModeToggle`.
 * @since TBD Remove the `banner` slot; it renders in the notice area.
 * @since TBD
 */
export type ListTablePageHeaderProps = {
    title: string;
    /** The screen's header buttons. */
    children?: ReactNode;
    /** The test-mode switch's badge text and accessible name, on screens that have one. */
    testModeToggle?: TestModeToggle;
    testMode: boolean;
    onChangeTestMode: (isOn: boolean) => void;
    statsConfig?: Record<string, StatConfig>;
    stats?: Record<string, number>;
};

/**
 * The Give part of a list screen above the list: title, header buttons, test-mode toggle
 * and stat tiles.
 *
 * @since TBD Take the test-mode switch as `testModeToggle`.
 * @since TBD
 */
export default function ListTablePageHeader({
    title,
    children,
    testModeToggle,
    testMode,
    onChangeTestMode,
    statsConfig,
    stats,
}: ListTablePageHeaderProps) {
    return (
        <>
            <header className={styles.pageHeader}>
                <div className={styles.flexRow}>
                    <GiveIcon size={'2.25rem'} />
                    <h1 className={styles.pageTitle}>{title}</h1>
                    {testModeToggle && testMode && <span className={styles.testModeBadge}>{testModeToggle.label}</span>}
                </div>
                {children && <div className={styles.flexRow}>{children}</div>}
            </header>

            <div className={cx('wp-header-end', 'hidden')} />

            {testModeToggle && (
                <div className={styles.filtersRow}>
                    <ToggleSwitch
                        ariaLabel={testModeToggle.ariaLabel || undefined}
                        onChange={onChangeTestMode}
                        checked={testMode}
                    />
                </div>
            )}
            {statsConfig && stats && <ListTableStats config={statsConfig} values={stats} />}
        </>
    );
}
