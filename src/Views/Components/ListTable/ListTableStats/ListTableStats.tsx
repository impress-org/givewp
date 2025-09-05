import StatWidget from "@givewp/admin/components/StatWidget";
import { __ } from "@wordpress/i18n";
import styles from "./styles.module.scss";

/**
 * @unreleased
 */
export type StatConfig = {
    label: string;
    inActive?: boolean;
    href?: string;
    toolTipDescription?: string;
};

/**
 * @unreleased
 */
type ListTableStatsProps = {
    config: Record<string, StatConfig>;
    values: Record<string, number>;
};

/**
 * @unreleased
 */
export default function ListTableStats({config, values}: ListTableStatsProps) {
    return (
        <section className={styles.tableStatsContainer} role="region" aria-label={__('Donation statistics', 'give')}>
            {Object.entries(config).map(([key, statConfig]) =>  { 
                return(<StatWidget
                    key={key}
                    className={styles.tableStatWidget}
                    {...statConfig}
                    value={values[key]}
                />);
            })}
        </section>
    );
}