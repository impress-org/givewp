import { Donation } from '@givewp/donations/admin/components/types';
import { getCompletedDonations } from '../SubscriptionStats';
import { ApexOptions } from 'apexcharts';

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

/**
 * Get completed donations for the current year
 */
export const getCurrentYearCompletedDonations = (donations: Donation[]): Donation[] => {
    const currentYear = new Date().getFullYear();
    const completedDonations = getCompletedDonations(donations);
    
    return completedDonations?.filter(donation => {
        const donationDate = new Date(donation.createdAt.date);
        return donationDate.getFullYear() === currentYear;
    });
};

/**
 * Calculate total contributions from completed donations
 */
export const calculateTotalContributions = (donations: Donation[]): number => {
    const completedDonations = getCurrentYearCompletedDonations(donations);
    return completedDonations?.reduce((acc, donation) => acc + Number(donation.amount.value), 0);
};

/**
 * Calculate progress percentage based on completed vs projected amounts
 */
export const calculateProgressPercentage = (completedAmount: number, projectedAmount: number): number => {
    if (projectedAmount <= 0) {
        return 0;
    }
    
    const progress = Math.ceil((completedAmount / projectedAmount) * 100);
    return Math.min(progress, 100);
};
