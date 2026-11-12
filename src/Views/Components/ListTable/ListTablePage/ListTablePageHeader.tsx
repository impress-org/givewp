import type {ReactNode} from 'react';
import type {View} from '@wordpress/dataviews';
import cx from 'classnames';
import {FormToggle} from '@wordpress/components';
import {useInstanceId} from '@wordpress/compose';
import {__, sprintf} from '@wordpress/i18n';
import GiveMark from '../GiveMark';
import TestModeBadge from '../TestModeBadge';
import StatTiles from '../StatTiles';
import type {ListTableStats, StatsStatus} from '../hooks/useListTableStats';
import type {StatConfig, TestModeToggle} from './types';
import styles from './ListTablePage.module.scss';

/**
 * @since TBD Render the stat tiles whenever `statsConfig` is set, with a status.
 * @since TBD Take the test-mode switch as `testModeToggle`.
 * @since TBD Add `filters`, `availableFilterIds` and `onChangeFilters` for the tiles that filter.
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
    /** The view's filters, which press the tiles that stand for them. */
    filters?: View['filters'];
    /** The ids of the filters the screen declares. */
    availableFilterIds?: string[];
    /** Called with the new filters when a tile is toggled. */
    onChangeFilters?: (filters: View['filters']) => void;
};

/**
 * The Give part of a list screen above the list: title, header buttons, banner, test-mode toggle
 * and stat tiles.
 *
 * @since TBD Render the stat tiles with a status, take the test-mode switch as `testModeToggle` and show it in the header with the stock form toggle.
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
    filters,
    availableFilterIds,
    onChangeFilters,
}: ListTablePageHeaderProps) {
    /* translators: %s: the screen's title (for example "Donors") */
    const switchId = `givewp-test-mode-switch-${useInstanceId(ListTablePageHeader)}`;
    const statsLabel = sprintf(__('%s statistics', 'give'), title);

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
                        {children}
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
                    </div>
                )}
            </header>

            <div className={cx('wp-header-end', 'hidden')} />

            {banner && <section role="banner">{banner()}</section>}
            {statsConfig && (
                <StatTiles
                    config={statsConfig}
                    stats={stats}
                    status={statsStatus}
                    label={statsLabel}
                    filters={filters}
                    availableFilterIds={availableFilterIds}
                    onChangeFilters={onChangeFilters}
                />
            )}
        </>
    );
}
