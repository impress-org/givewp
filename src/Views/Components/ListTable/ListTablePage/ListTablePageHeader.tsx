import type {ReactNode} from 'react';
import cx from 'classnames';
import {GiveIcon} from '@givewp/components';
import ToggleSwitch from '@givewp/components/ListTable/ToggleSwitch';
import ListTableStats, {StatConfig} from '../ListTableStats/ListTableStats';
import type {FilterConfig} from './types';
import styles from './ListTablePage.module.scss';

/**
 * @since TBD
 */
export type ListTablePageHeaderProps = {
    title: string;
    /** The screen's header buttons. */
    children?: ReactNode;
    banner?: () => ReactNode;
    /** The test-mode toggle's settings, on screens that have one. */
    testModeFilter?: FilterConfig;
    testMode: boolean;
    onChangeTestMode: (isOn: boolean) => void;
    statsConfig?: Record<string, StatConfig>;
    stats?: Record<string, number>;
};

/**
 * The Give part of a list screen above the list: title, header buttons, banner, test-mode toggle
 * and stat tiles.
 *
 * @since TBD
 */
export default function ListTablePageHeader({
    title,
    children,
    banner,
    testModeFilter,
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
                    {testModeFilter && testMode && <span className={styles.testModeBadge}>{testModeFilter.text}</span>}
                </div>
                {children && <div className={styles.flexRow}>{children}</div>}
            </header>

            <div className={cx('wp-header-end', 'hidden')} />

            {banner && <section role="banner">{banner()}</section>}
            {testModeFilter && (
                <div className={styles.filtersRow}>
                    <ToggleSwitch ariaLabel={testModeFilter.ariaLabel} onChange={onChangeTestMode} checked={testMode} />
                </div>
            )}
            {statsConfig && stats && <ListTableStats config={statsConfig} values={stats} />}
        </>
    );
}
