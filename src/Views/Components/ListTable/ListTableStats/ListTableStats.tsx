import StatWidget from "@givewp/admin/components/StatWidget";
import { __ } from "@wordpress/i18n";
import styles from "./styles.module.scss";

/**
 * @since 4.10.0
 */
export type StatConfig = {
    label: string;
    upgrade?: {
        href: string;
        tooltip: string;
    };
};

/**
 * @since 4.10.0
 */
type ListTableStatsProps = {
    config: Record<string, StatConfig>;
    values: Record<string, number>;
};

/**
 * @since 4.10.0
 */
export default function ListTableStats({config, values}: ListTableStatsProps) {
    return (
        <section className={styles.tableStatsContainer} role="region" aria-label={__('Donation statistics', 'give')}>
            {Object.entries(config)
                .filter(([key]) => Object.keys(values).includes(key))
                .map(([key, statConfig]) => (
                    <StatWidget
                    key={key}
                    className={styles.tableStatWidget}
                    {...statConfig}
                    value={values?.[key] ?? 0}
                />
            ))}
        </section>
    );
}
