import cx from 'classnames';
import {__} from '@wordpress/i18n';
import DashPlaceholder from '../DashPlaceholder';
import Skeleton from '../Skeleton';
import './styles.scss';

export type StatTileState = 'loading' | 'ready' | 'refreshing' | 'error' | 'upgrade';

export interface StatTileProps {
    label: string;
    /** The server's display text from the stats `formatted` map. */
    value?: string;
    state: StatTileState;
    /** Shown in place of the number when the add-on is missing. */
    upgrade?: {href: string; message: string};
    /** Set by the row when the filter this tile stands for is on. */
    isPressed?: boolean;
    /**
     * Whether a screen reader hears the pressed state. False for the Total tile: it is lit when no filter
     * is on, but it does not toggle, so it is announced as a plain button. Defaults to true.
     */
    announcePressed?: boolean;
    /** Present: the tile is a toggle button. Never a button in the `upgrade` state. */
    onToggle?: () => void;
}

function Value({state, value, upgrade}: Pick<StatTileProps, 'state' | 'value' | 'upgrade'>) {
    if (state === 'loading') {
        return (
            <>
                <Skeleton inlineSize="5ch" blockSize="var(--givewp-font-size-heading-md)" />
                <span className="screen-reader-text">{__('Loading', 'give')}</span>
            </>
        );
    }

    if (state === 'error') {
        return <DashPlaceholder label={__('Statistic could not be loaded', 'give')} />;
    }

    if (state === 'upgrade') {
        return (
            <span className="givewp-stat-tile__upgrade">
                {upgrade?.message && <span className="givewp-stat-tile__upgrade-message">{upgrade.message}</span>}
                {upgrade?.href && (
                    <a className="givewp-stat-tile__upgrade-link" href={upgrade.href}>
                        {__('Upgrade', 'give')}
                    </a>
                )}
            </span>
        );
    }

    return (
        <span
            className={cx('givewp-stat-tile__value', {'givewp-stat-tile__value--refreshing': state === 'refreshing'})}
        >
            {value}
        </span>
    );
}

/**
 * One aggregate figure of a list screen. It keeps the same size in every state. With `onToggle` it
 * is a toggle button (label, then value); without it, plain text. A background refresh keeps the old
 * value on screen, dimmed, and announces nothing.
 *
 * @since TBD
 */
export default function StatTile({
    label,
    value,
    state,
    upgrade,
    isPressed = false,
    announcePressed = true,
    onToggle,
}: StatTileProps) {
    const content = (
        <>
            <span className="givewp-stat-tile__label">{label}</span>
            <span className="givewp-stat-tile__body">
                <Value state={state} value={value} upgrade={upgrade} />
            </span>
        </>
    );
    const className = cx('givewp-stat-tile', {
        'givewp-stat-tile--toggle': Boolean(onToggle) && state !== 'upgrade',
        'givewp-stat-tile--pressed': isPressed,
    });

    if (onToggle && state !== 'upgrade') {
        return (
            <button
                type="button"
                className={className}
                aria-pressed={announcePressed ? isPressed : undefined}
                onClick={onToggle}
            >
                {content}
            </button>
        );
    }

    return <div className={className}>{content}</div>;
}
