import type {ReactNode} from 'react';
import cx from 'classnames';
import {GiveIcon} from '@givewp/components';
import ToggleSwitch from '@givewp/components/ListTable/ToggleSwitch';
import {__, sprintf} from '@wordpress/i18n';
import StatTiles from '../StatTiles';
import type {ListTableStats, StatsStatus} from '../hooks/useListTableStats';
import type {StatConfig, TestModeToggle} from './types';
import styles from './ListTablePage.module.scss';

/**
 * @since TBD Render the stat tiles whenever `statsConfig` is set, with a status.
 * @since TBD Take the test-mode switch as `testModeToggle`.
 * @since TBD
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
    statsConfig?: Record<string, StatConfig>;
    stats?: ListTableStats;
    /** Where the stats are: the tiles stay mounted in every status. */
    statsStatus?: StatsStatus;
};

/**
 * The Give part of a list screen above the list: title, header buttons, banner, test-mode toggle
 * and stat tiles.
 *
 * @since TBD Render the stat tiles whenever `statsConfig` is set, with a status.
 * @since TBD Take the test-mode switch as `testModeToggle`.
 * @since TBD
 */
export default function ListTablePageHeader({
    title,
    children,
    banner,
    testModeToggle,
    testMode,
    onChangeTestMode,
    statsConfig,
    stats,
    statsStatus = 'loading',
}: ListTablePageHeaderProps) {
    /* translators: %s: the screen's title (for example "Donors") */
    const statsLabel = sprintf(__('%s statistics', 'give'), title);

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

            {banner && <section role="banner">{banner()}</section>}
            {testModeToggle && (
                <div className={styles.filtersRow}>
                    <ToggleSwitch
                        ariaLabel={testModeToggle.ariaLabel || undefined}
                        onChange={onChangeTestMode}
                        checked={testMode}
                    />
                </div>
            )}
            {statsConfig && (
                <StatTiles
                    config={statsConfig}
                    stats={stats}
                    status={statsStatus}
                    label={statsLabel}
                />
            )}
        </>
    );
}
