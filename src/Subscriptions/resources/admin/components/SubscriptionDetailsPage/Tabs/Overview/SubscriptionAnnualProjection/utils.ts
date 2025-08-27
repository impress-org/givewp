import { Donation } from '@givewp/donations/admin/components/types';
import { ApexOptions } from 'apexcharts';

/**
 * @unreleased
 * Get completed donations for the current year
 */
export const getCurrentYearCompletedDonations = (donations: Donation[]): Donation[] => {
    const currentYear = new Date().getFullYear();
    return donations?.filter(donation => {
        const donationDate = new Date(donation.createdAt.date);
        return donationDate.getFullYear() === currentYear;
    });
};

/**
 * @unreleased
 * Calculate total contributions from completed donations
 */
export const calculateTotalContributions = (donations: Donation[]): number => {
    const total = donations?.reduce((acc, donation) => acc + Number(donation.amount.value), 0);

    if (isNaN(total)) {
        return 0;
    }

    return total;
};

/**
 * @unreleased
 * Calculate progress percentage based on completed vs projected amounts
 */
export const calculateProgressPercentage = (completedAmount: number, projectedAmount: number): number => {
    const progress = Math.ceil((completedAmount / projectedAmount) * 100);

    if (isNaN(progress)) {
        return 0;
    }

    return Math.min(progress, 100);
};

/**
 * @unreleased
 */
export const chartOptions = (label: string): ApexOptions => {
    return {
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
    };
};