import { Donation } from '@givewp/donations/admin/components/types';
import { ApexOptions } from 'apexcharts';
import { getCompletedDonations } from '../SubscriptionStats';

/**
 * @unreleased
 */
export type Period = 'day' | 'week' | 'month' | 'quarter' | 'year';

/**
 * @unreleased
 */
type GetNumericPeriodValueProps = {
    period: Period;
};

/**
 * Converts a subscription period to its numeric value in terms of occurrences per year
 *
 * @unreleased
 * @returns The number of periods in a year
 */
export const getNumericPeriodValue = ({ period }: GetNumericPeriodValueProps): number => {
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
 * @unreleased
 */
type CalculateDonationsUntilYearEndProps = {
    period: Period;
    frequency: number;
    startDate: Date;
    installments: number;
};

/**
 * Calculate the number of donations until the end of the current year
 * 
 * @unreleased
 * @returns The number of donations that will occur until year end
 */
export const calculateDonationsUntilYearEnd = ({ period, frequency, startDate, installments }: CalculateDonationsUntilYearEndProps): number => {
    try {
        const now = new Date();
        const currentYear = now.getFullYear();
        const yearEnd = new Date(currentYear, 11, 31, 23, 59, 59); // December 31st of current year
        
        // If subscription starts after current year, return 0
        if (startDate.getFullYear() > currentYear) {
            return 0;
        }
        
        // If subscription starts in current year, calculate from start date
        // If subscription started before current year, calculate from January 1st of current year
        const effectiveStartDate = startDate.getFullYear() === currentYear ? startDate : new Date(currentYear, 0, 1);
        
        // Calculate time difference
        const timeDiff = yearEnd.getTime() - effectiveStartDate.getTime();
        
        // Convert to days
        const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
        
        // Calculate donations based on period and frequency
        const periodsInYear = getNumericPeriodValue({ period });
        const daysPerPeriod = 365 / periodsInYear;
        const periodsUntilYearEnd = Math.floor(daysDiff / daysPerPeriod);
        
        // Apply frequency
        const donationsUntilYearEnd = Math.floor(periodsUntilYearEnd / frequency);
        
        // If installments are limited, respect that limit
        if (installments > 0) {
            return Math.min(installments, donationsUntilYearEnd);
        }
        
        return Math.max(0, donationsUntilYearEnd);
    } catch (error) {
        console.error('Error calculating donations until year end:', error);
        return 0;
    }
};

/**
 * @unreleased
 */
export const getCurrentYearCompletedDonations = (donations: Donation[]): Donation[] => {
    const completedDonations = getCompletedDonations(donations);
    const currentYear = new Date().getFullYear();

    return completedDonations?.filter(
        (donation) => donation.createdAt.date && new Date(donation.createdAt.date).getFullYear() === currentYear
    )
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