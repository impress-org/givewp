import apiFetch from '@wordpress/api-fetch';
import {useEffect, useState} from '@wordpress/element';

/**
 * @unreleased
 */
export interface DonorStatistics {
    statistics: {
        lifetimeDonations: number;
        highestDonation: number | null;
        averageDonation: number;
    };
    donorSince: string;
    firstDonation: {
        amount: string;
        date: string;
        formTitle: string;
    } | null;
    lastDonation: {
        amount: string;
        date: string;
        formTitle: string;
    } | null;
    preferredGivingType: 'single' | 'recurring';
    totalDonations: number;
}

/**
 * @unreleased
 */
interface UseStatisticsProps {
    donorId: number;
    campaignId?: number;
    mode?: 'live' | 'test';
}

/**
 * @unreleased
 * TODO: Refactor
 */
export const useStatistics = ({donorId, campaignId, mode = 'live'}: UseStatisticsProps) => {
    const [data, setData] = useState<DonorStatistics | null>(null);
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState<Error | null>(null);
    const [hasResolved, setHasResolved] = useState(false);

    useEffect(() => {
        const fetchData = async () => {
            if (donorId <= 0) {
                return;
            }

            setIsLoading(true);
            setError(null);

            try {
                const response = await apiFetch<DonorStatistics>({
                    path: `/givewp/v3/donors/${donorId}/statistics?mode=${mode}${
                        campaignId ? `&campaignId=${campaignId}` : ''
                    }`,
                });

                setData(response);
                setHasResolved(true);
            } catch (err) {
                setError(err instanceof Error ? err : new Error('Failed to fetch statistics'));
            } finally {
                setIsLoading(false);
            }
        };

        fetchData();
    }, [donorId, campaignId, mode]);

    return {
        data,
        loading: isLoading && !hasResolved,
        hasResolved,
        error,
    };
};
