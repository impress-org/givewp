import type {View} from '@wordpress/dataviews';
import StatTile, {type StatTileState} from '../StatTile';
import type {ListTableStats, StatsStatus} from '../hooks/useListTableStats';
import type {StatConfig} from '../ListTablePage/types';
import './styles.scss';

interface StatTilesProps {
    /** One tile per key, in this order. */
    config: Record<string, StatConfig>;
    stats?: ListTableStats;
    status: StatsStatus;
    /** The accessible name of the row, which names the screen ("Donors statistics"). */
    label: string;
    /** Wired by the tile-to-filter mapping; not read yet. */
    filters?: View['filters'];
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
 * @since TBD
 */
export default function StatTiles({config, stats, status, label}: StatTilesProps) {
    return (
        <section className="givewp-stat-tiles" role="region" aria-label={label}>
            {Object.entries(config).map(([key, statConfig]) => {
                const value = stats?.formatted[key];

                return (
                    <StatTile
                        key={key}
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
