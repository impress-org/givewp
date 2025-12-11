import {filterOptions} from '../CampaignStats';
import styles from './styles.module.scss';

type DateRangeFiltersProps = {
    options: typeof filterOptions;
    onSelect: (value: number) => void;
    selected: number;
}

const DateRangeFilters = ({options, onSelect, selected}: DateRangeFiltersProps) => {
    return (
        <div className={styles.dateRangeFilter}>
            {options.map((option, index) => (
                <button
                    className={selected === option.value ? styles.selectedDateRange : ''}
                    key={index}
                    onClick={() => onSelect(option.value)}
                >
                    {option.label}
                </button>
            ))}
        </div>
    );
};
export default DateRangeFilters;
