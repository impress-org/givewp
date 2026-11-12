import type {View} from '@wordpress/dataviews';
import StatTile, {type StatTileState} from '../StatTile';
import type {ListTableStats, StatsStatus} from '../hooks/useListTableStats';
import type {StatConfig} from '../ListTablePage/types';
import {isTilePressed, tileFilterOf, toggleTileFilter} from './tileFilters';
import './styles.scss';

interface StatTilesProps {
    /** One tile per key, in this order. */
    config: Record<string, StatConfig>;
    stats?: ListTableStats;
    status: StatsStatus;
    /** The accessible name of the row, which names the screen ("Donors statistics"). */
    label: string;
    /** The view's filters. A tile is pressed only because of what is in here. */
    filters?: View['filters'];
    /** The ids of the filters the screen declares; a tile for any other filter is not a button. */
    availableFilterIds?: string[];
    onChangeFilters?: (filters: View['filters']) => void;
}

function tileState(statConfig: StatConfig, status: StatsStatus, hasValue: boolean): StatTileState {
    if (statConfig.upgrade) {
        return 'upgrade';
    }

    if (status === 'loading' || status === 'error') {
        return status;
    }

    // The endpoint did not send this key: show the error dash, not an empty tile.
    return hasValue ? status : 'error';
}

/**
 * The row of stat tiles above a list. Every tile in the config is always rendered, so the row does
 * not change size while the numbers load or refresh. Values are the server's display text.
 *
 * A tile with a declared filter is a toggle, and it is pressed when the view's filters say so (never
 * from state of its own), so a value set in the toolbar presses the matching tile.
 *
 * @since TBD
 */
export default function StatTiles({
    config,
    stats,
    status,
    label,
    filters = [],
    availableFilterIds = [],
    onChangeFilters,
}: StatTilesProps) {
    return (
        <section className="givewp-stat-tiles" aria-label={label}>
            {Object.entries(config).map(([key, statConfig]) => {
                const value = stats?.formatted[key];
                const tileFilter = onChangeFilters ? tileFilterOf(statConfig, availableFilterIds) : undefined;

                return (
                    <StatTile
                        key={key}
                        isPressed={tileFilter ? isTilePressed(tileFilter, filters) : false}
                        announcePressed={tileFilter?.value !== undefined}
                        onToggle={
                            tileFilter ? () => onChangeFilters?.(toggleTileFilter(filters, tileFilter)) : undefined
                        }
                        label={statConfig.label}
                        value={value}
                        state={tileState(statConfig, status, value !== undefined)}
                        upgrade={
                            statConfig.upgrade
                                ? {href: statConfig.upgrade.href, message: statConfig.upgrade.tooltip}
                                : undefined
                        }
                    />
                );
            })}
        </section>
    );
}
