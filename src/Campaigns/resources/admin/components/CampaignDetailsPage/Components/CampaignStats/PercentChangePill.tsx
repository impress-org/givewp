import styles from './styles.module.scss';
import type {PercentChangePillProps} from './types';

/**
 * @since 4.0.0
 */
const getPercentageChange = (previousValue: number, currentValue: number) => {
    if (previousValue === 0) {
        return currentValue === 0 ? 0 : 100;
    }

    const value = currentValue - previousValue / Math.abs(previousValue) * 100;
    if (value !== 100) {
        return value.toFixed(1);
    }

    return value;
}

const IconArrowUp = () => (
    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M5.833 11.667 10 7.5l4.167 4.167H5.833z" fill="#2D802F"/>
    </svg>
);

const IconArrowDown = () => (
    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M10 12.5 5.833 8.335h8.334L10 12.501z" fill="#D92D0B"/>
    </svg>
)

const IconArrowRight = () => (
    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M8.334 14.167V5.834l4.166 4.167-4.166 4.166z" fill="#060C1A"/>
    </svg>
)

/**
 * @since 4.0.0
 */
const PercentChangePill = ({value, comparison}: PercentChangePillProps) => {
    const change = getPercentageChange(comparison, value);

    const [color, backgroundColor, symbol] =
        change === 0
            ? ['#060c1a', '#f2f2f2', <IconArrowRight />]
            : Number(change) > 0
            ? ['#2d802f', '#f2fff3', <IconArrowUp />]
            : ['#e35f45', '#fff4f2', <IconArrowDown />];

    return (
        <div className={styles.percentChangePill} style={{backgroundColor: backgroundColor, color: color}}>
            {symbol} <span>{change}%</span>
        </div>
    );
};

export default PercentChangePill;
