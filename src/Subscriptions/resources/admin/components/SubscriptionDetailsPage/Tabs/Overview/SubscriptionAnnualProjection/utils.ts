import { ApexOptions } from 'apexcharts';

/**
 * @unreleased
 */
export type Period = 'day' | 'week' | 'month' | 'quarter' | 'year';

/**
 * Converts a subscription period to its numeric value in terms of occurrences per year
 *
 * @unreleased
 * @returns The number of periods in a year
 */
export const getNumericPeriodValue = (period: Period): number => {
    const periodMap: Record<Period, number> = {
        day: 365,
        week: 52,
        month: 12,
        quarter: 4,
        year: 1,
    };

    if (!(period in periodMap)) {
        throw new Error(`Invalid period value: ${period}`);
    }

    return periodMap[period];
};

/**
 * Calculate the number of donations in a year based on period and frequency
 * 
 * @unreleased
 * @returns The number of donations that will occur in a year
 */
export const calculateAnnualDonations = (period: Period, frequency: number): number => {
    try {
        const periodsInYear = getNumericPeriodValue(period);
        return Math.floor(periodsInYear / frequency);
    } catch (error) {
        console.error('Error calculating annual donations:', error);
        return 12; // Default to monthly (12 per year) if there's an error
    }
};

/**
 * @unreleased
 */
export const chartOptions = (label: string): ApexOptions => {
    return{
        chart: {
            height: 1024,
            type: 'radialBar',  
        },
        plotOptions: {
            radialBar: {
                hollow: {
                    margin: 15,
                    size: '60%',
                },
                dataLabels: {
                    /**
                     * The "name" is the top label, here it is the value/amount
                     * The "value" is the percent progress
                     */
                    name: {
                        offsetY: 20,
                        show: true,
                        color: '#4B5563',
                        fontSize: '12px',
                    },
                    value: {
                        offsetY: -20,
                        color: '#060C1A',
                        fontSize: '24px',
                        show: true,
                    },
                },
            },
        },
        colors: ['#459948'],
        labels: [label],
    }
};  